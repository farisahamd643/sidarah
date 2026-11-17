'use client';

import { useState } from 'react';
import { Icon } from './Icons';

export default function PasswordInput({
  id,
  name = 'password',
  value,
  onChange,
  placeholder = 'Masukkan password',
  required = true,
  minLength = 8,
  ...props
}) {
  const [show, setShow] = useState(false);

  return (
    <div className="fld pw">
      <Icon name="kunci" />
      <input
        type={show ? 'text' : 'password'}
        id={id}
        name={name}
        value={value}
        onChange={onChange}
        required={required}
        minLength={minLength}
        placeholder={placeholder}
        {...props}
      />
      <button
        type="button"
        className={`eye ${show ? 'on' : ''}`}
        onClick={() => setShow(!show)}
        aria-label={show ? 'Sembunyikan password' : 'Tampilkan password'}
      >
        <Icon name="mata" className="" />
      </button>
    </div>
  );
}
