import { useState } from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { createFileRoute } from '@tanstack/react-router';
import { createTournament } from '../api/tournaments';

const SPORTS = ['football', 'basketball', 'rugby', 'handball', 'volleyball'];

export const Route = createFileRoute('/_app/admin/tournaments/create')({
  component: CreateTournamentPage,
});

export default function CreateTournamentPage() {
  const qc = useQueryClient();
  const [success, setSuccess] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState('');
  const [name, setName] = useState('');
  const [slug, setSlug] = useState('');
  const [sport, setSport] = useState('football');

  const mutation = useMutation({
    mutationFn: () => createTournament({ name, slug, sport }),
    onSuccess: () => {
      setSuccess(true);
      setErrors({});
      setGeneralError('');
      void qc.invalidateQueries({ queryKey: ['tournaments'] });
    },
    onError: (err: unknown) => {
      const e = err as { errors?: Record<string, string[]>; message?: string };
      if (e.errors) {
        setErrors(e.errors);
        setGeneralError('');
        return;
      }
      setGeneralError(e.message ?? 'An unexpected error occurred.');
    },
  });

  function handleSubmit(ev: React.FormEvent) {
    ev.preventDefault();
    setSuccess(false);
    setErrors({});
    setGeneralError('');
    if (!name.trim()) {
      setErrors({ name: ['Name is required.'] });
      return;
    }
    if (!slug.trim()) {
      setErrors({ slug: ['Slug is required.'] });
      return;
    }
    mutation.mutate();
  }

  return (
    <main className="mx-auto max-w-xl p-4">
      <h1 className="mb-4 text-2xl font-bold">Create Tournament</h1>

      {success && (
        <p className="mb-4 rounded bg-green-100 p-2 text-green-800">
          Tournament created successfully!
        </p>
      )}

      {generalError && (
        <p className="mb-4 rounded bg-red-100 p-2 text-red-800">{generalError}</p>
      )}

      <form onSubmit={handleSubmit} className="space-y-4" noValidate>
        <div>
          <label htmlFor="name" className="block text-sm font-medium">
            Name
          </label>
          <input
            id="name"
            value={name}
            onChange={(e) => setName(e.target.value)}
            className="mt-1 w-full rounded border p-2"
          />
          {errors.name && <p className="text-sm text-red-600">{errors.name[0]}</p>}
        </div>

        <div>
          <label htmlFor="slug" className="block text-sm font-medium">
            Slug
          </label>
          <input
            id="slug"
            value={slug}
            onChange={(e) => setSlug(e.target.value)}
            className="mt-1 w-full rounded border p-2"
          />
          {errors.slug && <p className="text-sm text-red-600">{errors.slug[0]}</p>}
        </div>

        <div>
          <label htmlFor="sport" className="block text-sm font-medium">
            Sport
          </label>
          <select
            id="sport"
            value={sport}
            onChange={(e) => setSport(e.target.value)}
            className="mt-1 w-full rounded border p-2"
          >
            {SPORTS.map((s) => (
              <option key={s} value={s}>
                {s}
              </option>
            ))}
          </select>
          {errors.sport && <p className="text-sm text-red-600">{errors.sport[0]}</p>}
        </div>

        <button
          type="submit"
          disabled={mutation.isPending}
          className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
        >
          {mutation.isPending ? 'Creating…' : 'Create'}
        </button>
      </form>
    </main>
  );
}
