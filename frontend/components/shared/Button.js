export default function Button({
  children,
  variant = 'primary', // 'primary', 'outline', 'small', 'block'
  type = 'button',
  disabled = false,
  className = '',
  onClick,
  ...props
}) {
  let btnClass = 'btn';
  if (variant === 'outline') btnClass += ' o';
  if (variant === 'small') btnClass += ' s';
  if (variant === 'block') btnClass += ' blok';

  return (
    <button
      type={type}
      disabled={disabled}
      onClick={onClick}
      className={`${btnClass} ${className}`.trim()}
      {...props}
    >
      {children}
    </button>
  );
}
