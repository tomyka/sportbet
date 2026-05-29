import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { login as apiLogin } from './authApi';

type Props = { onSuccess?: () => void };

export function LoginForm({ onSuccess }: Props = {}) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  const mutation = useMutation({
    mutationFn: () => apiLogin(email, password),
    onSuccess: () => onSuccess?.(),
  });

  return (
    <form
      onSubmit={(e) => { e.preventDefault(); mutation.mutate(); }}
      className="space-y-3"
    >
      <h2 className="text-xl font-semibold">Sign in</h2>
      <label className="block">
        Email
        <input type="email" value={email} onChange={(e) => setEmail(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1" required />
      </label>
      <label className="block">
        Password
        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1" required />
      </label>
      <button type="submit" disabled={mutation.isPending}
        className="w-full bg-blue-600 text-white py-2 rounded">
        {mutation.isPending ? 'Signing in…' : 'Sign in'}
      </button>
      {mutation.isError && <p role="alert" className="text-red-600 text-sm">Login failed</p>}
    </form>
  );
}
