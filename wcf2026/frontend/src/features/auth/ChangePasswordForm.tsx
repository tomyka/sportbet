import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { changePassword } from './authApi';

export function ChangePasswordForm() {
  const [current, setCurrent] = useState('');
  const [password, setPassword] = useState('');
  const [confirm, setConfirm] = useState('');
  const [done, setDone] = useState(false);

  const mutation = useMutation({
    mutationFn: () => changePassword({ current_password: current, password, password_confirmation: confirm }),
    onSuccess: () => setDone(true),
  });

  return (
    <section className="space-y-3">
      <h3 className="font-medium">Change password</h3>
      {done && <p className="text-green-600">Password changed successfully.</p>}
      <form onSubmit={(e) => { e.preventDefault(); mutation.mutate(); }} className="space-y-2">
        <label className="block">
          Current Password
          <input type="password" value={current} onChange={(e) => setCurrent(e.target.value)}
            className="block w-full border rounded px-2 py-1 mt-1" required />
        </label>
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
        <button type="submit" disabled={mutation.isPending} className="bg-gray-800 text-white px-4 py-2 rounded">
          {mutation.isPending ? 'Changing…' : 'Change password'}
        </button>
        {mutation.isError && <p role="alert" className="text-red-600 text-sm">Failed to change password.</p>}
      </form>
    </section>
  );
}
