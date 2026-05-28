import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { resetPassword } from './authApi';

type Props = { token: string; email: string; onSuccess: () => void };

export function ResetPasswordForm({ token, email, onSuccess }: Props) {
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [done, setDone] = useState(false);

  const mutation = useMutation({
    mutationFn: () => resetPassword({ token, email, password, password_confirmation: confirm }),
    onSuccess: () => { setDone(true); onSuccess(); },
  });

  if (done) return <p>Your password has been reset. You may now log in.</p>;

  return (
    <form onSubmit={(e) => { e.preventDefault(); mutation.mutate(); }} className="space-y-3">
      <h2 className="text-xl font-semibold">Set new password</h2>
      <label className="block">
        New Password
        <input type="password" value={password} onChange={(e) => setPassword(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1" required />
      </label>
      <label className="block">
        Confirm Password
        <input type="password" value={confirm} onChange={(e) => setConfirm(e.target.value)}
          className="block w-full border rounded px-2 py-1 mt-1" required />
      </label>
      <button type="submit" disabled={mutation.isPending} className="w-full bg-blue-600 text-white py-2 rounded">
        {mutation.isPending ? 'Resetting…' : 'Reset password'}
      </button>
      {mutation.isError && <p role="alert" className="text-red-600 text-sm">Reset failed.</p>}
    </form>
  );
}
