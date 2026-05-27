import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { apiClient, ensureCsrfCookie } from '../../lib/api-client';

type Me = { id: number; email: string; name: string };

export function LoginForm() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [me, setMe] = useState<Me | null>(null);

  const mutation = useMutation({
    mutationFn: async () => {
      await ensureCsrfCookie();
      const res = await apiClient.post<{ data: Me }>('/api/v1/auth/login', { email, password });
      return res.data.data;
    },
    onSuccess: setMe,
  });

  if (me) return <p>{me.email}</p>;

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        mutation.mutate();
      }}
      className="p-4 space-y-2"
    >
      <label className="block">
        Email
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="block border"
        />
      </label>
      <label className="block">
        Password
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className="block border"
        />
      </label>
      <button type="submit" disabled={mutation.isPending}>
        Sign in
      </button>
      {mutation.isError && <p role="alert">Login failed</p>}
    </form>
  );
}
