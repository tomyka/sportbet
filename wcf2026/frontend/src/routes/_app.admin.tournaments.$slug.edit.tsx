import { useEffect, useState } from 'react';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { createFileRoute, useParams } from '@tanstack/react-router';
import { tournamentQuery, updateTournament } from '../api/tournaments';

const SPORTS = ['football', 'basketball', 'rugby', 'handball', 'volleyball'];
const STATUSES = ['draft', 'open', 'locked', 'finished'];

interface Props {
  slug?: string;
}

export const Route = createFileRoute('/_app/admin/tournaments/$slug/edit')({
  component: EditTournamentRoute,
});

function EditTournamentRoute() {
  const { slug } = useParams({ strict: false }) as { slug?: string };

  return <EditTournamentPage slug={slug} />;
}

export default function EditTournamentPage({ slug: slugProp }: Props) {
  const slug = slugProp ?? '';
  const qc = useQueryClient();
  const [success, setSuccess] = useState(false);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState('');
  const [name, setName] = useState('');
  const [sport, setSport] = useState('football');
  const [status, setStatus] = useState('draft');

  const { data, isPending } = useQuery({
    ...tournamentQuery(slug),
    enabled: Boolean(slug),
  });

  useEffect(() => {
    if (data?.data) {
      setName(data.data.name);
      setSport(data.data.sport);
      setStatus(data.data.status);
    }
  }, [data]);

  const mutation = useMutation({
    mutationFn: () => updateTournament(slug, { name, sport, status }),
    onSuccess: () => {
      setSuccess(true);
      setErrors({});
      setGeneralError('');
      void qc.invalidateQueries({ queryKey: ['tournament', slug] });
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

  if (!slug) return <p>Tournament not found.</p>;
  if (isPending) return <p>Loading...</p>;

  function handleSubmit(ev: React.FormEvent) {
    ev.preventDefault();
    setSuccess(false);
    setErrors({});
    setGeneralError('');
    mutation.mutate();
  }

  return (
    <main className="mx-auto max-w-xl p-4">
      <h1 className="mb-4 text-2xl font-bold">Edit Tournament</h1>

      {success && (
        <p className="mb-4 rounded bg-green-100 p-2 text-green-800">
          Changes saved successfully!
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
        </div>

        <div>
          <label htmlFor="status" className="block text-sm font-medium">
            Status
          </label>
          <select
            id="status"
            value={status}
            onChange={(e) => setStatus(e.target.value)}
            className="mt-1 w-full rounded border p-2"
          >
            {STATUSES.map((s) => (
              <option key={s} value={s}>
                {s}
              </option>
            ))}
          </select>
        </div>

        <button
          type="submit"
          disabled={mutation.isPending}
          className="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
        >
          {mutation.isPending ? 'Saving…' : 'Save'}
        </button>
      </form>
    </main>
  );
}
