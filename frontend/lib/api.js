import { getToken, logout } from './auth';

const BASE_URL = process.env.NEXT_PUBLIC_API_URL || 'http://localhost:3000';

export async function request(endpoint, { method = 'GET', body, headers = {}, token } = {}) {
  const url = endpoint.startsWith('http') ? endpoint : `${BASE_URL}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;
  
  const activeToken = token !== undefined ? token : getToken();
  
  const reqHeaders = {
    'Accept': 'application/json',
    ...headers,
  };

  if (activeToken) {
    reqHeaders['Authorization'] = `Bearer ${activeToken}`;
  }

  const options = {
    method,
    headers: reqHeaders,
  };

  if (body !== undefined) {
    reqHeaders['Content-Type'] = 'application/json';
    options.body = JSON.stringify(body);
  }

  let res;
  try {
    res = await fetch(url, options);
  } catch (err) {
    throw new Error('Gagal terhubung ke server backend. Pastikan backend aktif.');
  }

  let data = null;
  const contentType = res.headers.get('content-type');
  if (contentType && contentType.includes('application/json')) {
    try {
      data = await res.json();
    } catch {
      data = null;
    }
  }

  if (!res.ok) {
    if (res.status === 401) {
      // Jika token kedaluwarsa atau tidak valid di area terproteksi
      if (typeof window !== 'undefined' && !window.location.pathname.includes('/login')) {
        logout();
      }
    }
    const pesan = data?.pesan || `Terjadi kesalahan (Kode status: ${res.status})`;
    const error = new Error(pesan);
    error.status = res.status;
    error.data = data;
    throw error;
  }

  return data;
}

export const api = {
  get: (endpoint, options) => request(endpoint, { ...options, method: 'GET' }),
  post: (endpoint, body, options) => request(endpoint, { ...options, method: 'POST', body }),
  put: (endpoint, body, options) => request(endpoint, { ...options, method: 'PUT', body }),
  patch: (endpoint, body, options) => request(endpoint, { ...options, method: 'PATCH', body }),
  delete: (endpoint, options) => request(endpoint, { ...options, method: 'DELETE' }),
};

export default api;
