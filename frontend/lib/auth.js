import Cookies from 'js-cookie';

const TOKEN_KEY = 'sidarah_token';
const USER_KEY = 'sidarah_user';

export function getToken() {
  if (typeof window === 'undefined') return null;
  return Cookies.get(TOKEN_KEY) || localStorage.getItem(TOKEN_KEY) || null;
}

export function setToken(token) {
  if (!token) return;
  // Token berlaku 1 hari sesuai konfigurasi backend JWT
  Cookies.set(TOKEN_KEY, token, { expires: 1, sameSite: 'lax' });
  if (typeof window !== 'undefined') {
    localStorage.setItem(TOKEN_KEY, token);
  }
}

export function removeToken() {
  Cookies.remove(TOKEN_KEY);
  if (typeof window !== 'undefined') {
    localStorage.removeItem(TOKEN_KEY);
  }
}

export function getUser() {
  if (typeof window === 'undefined') return null;
  const userJson = Cookies.get(USER_KEY) || localStorage.getItem(USER_KEY);
  if (!userJson) return null;
  try {
    return JSON.parse(userJson);
  } catch {
    return null;
  }
}

export function setUser(user) {
  if (!user) return;
  const str = JSON.stringify(user);
  Cookies.set(USER_KEY, str, { expires: 1, sameSite: 'lax' });
  if (typeof window !== 'undefined') {
    localStorage.setItem(USER_KEY, str);
  }
}

export function removeUser() {
  Cookies.remove(USER_KEY);
  if (typeof window !== 'undefined') {
    localStorage.removeItem(USER_KEY);
  }
}

export function logout() {
  removeToken();
  removeUser();
}
