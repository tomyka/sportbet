import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { forgotPassword } from './authApi';

export function ForgotPasswordForm() {
  const [email, setEmail] = useState('');
  const [sent, setSent] = useState(false);

  const mutation = useMutation({
    mutationFn: () => forgotPassword(email),
    onSuccess: () => setSent(true),
  });

  if (sent) return <p>Check your email for a password reset link.</p>;

  return (
    <form onSubmit={(e) => { e.preventDefault(); mutation.mutate(); }} className="space-y-3">
      <h2 className="text-xl font-semibold">Reset password</h2>
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
      <button type="submit" disabled={mutation.isPending} className="w-full bg-blue-600 text-white py-2 rounded">
        {mutation.isPending ? 'Sending…' : 'Send reset link'}
      </button>
    </form>
  );
}
