import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { register as apiRegister } from './authApi';

type Props = { onSuccess: () => void };

export function RegisterForm({ onSuccess }: Props) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [done, setDone] = useState(false);

  const mutation = useMutation({
    mutationFn: () =>
      apiRegister({ name, email, password, password_confirmation: confirm }),
    onSuccess: () => {
      setDone(true);
      onSuccess();
    },
  });

  if (done) {
    return <p>Check your email to verify your account.</p>;
  }

  return (
    <form
      onSubmit={(e) => { e.preventDefault(); mutation.mutate(); }}
      className="space-y-3"
    >
      <h2 className="text-xl font-semibold">Create account</h2>

      <label className="block">
        Name
        <input
          type="text"
          value={name}
          onChange={(e) => setName(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1"
          required
        />
      </label>

      <label className="block">
        Email
        <input
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1"
          required
        />
      </label>

      <label className="block">
        Password
        <input
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1"
          required
        />
      </label>

      <label className="block">
        Confirm Password
        <input
          type="password"
          value={confirm}
          onChange={(e) => setConfirm(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1"
          required
        />
      </label>

      <button
        type="submit"
        disabled={mutation.isPending}
        className="w-full bg-blue-600 text-white py-2 rounded"
      >
        {mutation.isPending ? 'Creating…' : 'Create account'}
      </button>

      {mutation.isError && (
        <p role="alert" className="text-red-600 text-sm">
          Registration failed. Please check your details.
        </p>
      )}
    </form>
  );
}
