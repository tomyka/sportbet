import { useState, useEffect } from 'react';
import { useMutation, useQuery } from '@tanstack/react-query';
import { fetchMe, updateProfile } from './authApi';
import { ChangePasswordForm } from './ChangePasswordForm';

export function ProfilePage() {
  const { data: me, isLoading } = useQuery({
    queryKey: ['auth', 'me'],
    queryFn: fetchMe,
  });

  const [name, setName] = useState('');
  const [displayName, setDisplayName] = useState('');
  const [saved, setSaved] = useState(false);

  useEffect(() => {
    if (me) {
      setName(me.name);
      setDisplayName(me.display_name ?? '');
    }
  }, [me]);

  const mutation = useMutation({
    mutationFn: () => updateProfile({ name, display_name: displayName || undefined }),
    onSuccess: () => setSaved(true),
  });

  if (isLoading) return <p>Loading…</p>;

  return (
    <div className="space-y-8 max-w-lg">
      <h2 className="text-2xl font-semibold">Profile</h2>

      <form onSubmit={(e) => { e.preventDefault(); setSaved(false); mutation.mutate(); }} className="space-y-3">
        <label className="block">
          Name
          <input type="text" value={name} onChange={(e) => setName(e.target.value)}
            className="block w-full border rounded px-2 py-1 mt-1" required />
        </label>
        <label className="block">
          Display Name
          <input type="text" value={displayName} onChange={(e) => setDisplayName(e.target.value)}
            className="block w-full border rounded px-2 py-1 mt-1" />
        </label>
        <button type="submit" disabled={mutation.isPending} className="bg-blue-600 text-white px-4 py-2 rounded">
          {mutation.isPending ? 'Saving…' : 'Save profile'}
        </button>
        {saved && <p className="text-green-600">Profile saved.</p>}
        {mutation.isError && <p role="alert" className="text-red-600 text-sm">Failed to save.</p>}
      </form>

      <hr />
      <ChangePasswordForm />
    </div>
  );
}
