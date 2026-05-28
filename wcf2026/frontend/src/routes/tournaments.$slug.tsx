import { useQuery } from '@tanstack/react-query';
import { createFileRoute, useParams } from '@tanstack/react-router';
import { NotFoundError, stagesQuery, tournamentQuery } from '../api/tournaments';

interface Props {
  slug?: string;
}

export const Route = createFileRoute('/tournaments/$slug')({
  component: TournamentDetailRoute,
});

function TournamentDetailRoute() {
  const { slug } = useParams({ strict: false }) as { slug?: string };

  return <TournamentDetailPage slug={slug} />;
}

export default function TournamentDetailPage({ slug: slugProp }: Props) {
  const slug = slugProp ?? '';

  const {
    data: tData,
    isPending: tPending,
    isError: tError,
    error,
  } = useQuery({
    ...tournamentQuery(slug),
    enabled: Boolean(slug),
  });

  const { data: sData, isPending: sPending } = useQuery({
    ...stagesQuery(slug),
    enabled: Boolean(slug) && !tError,
  });

  if (!slug) return <p>Tournament not found.</p>;
  if (tPending) return <p>Loading...</p>;

  if (tError) {
    if (error instanceof NotFoundError) return <p>Tournament not found.</p>;
    return <p>Failed to load tournament.</p>;
  }

  const tournament = tData.data;

  return (
    <main className="mx-auto max-w-3xl p-4">
      <h1 className="mb-2 text-2xl font-bold">{tournament.name}</h1>
      <p className="mb-4 capitalize text-gray-500">
        {tournament.sport} · {tournament.status}
      </p>

      <section>
        <h2 className="mb-2 text-lg font-semibold">Stages</h2>
        {sPending ? (
          <p>Loading stages...</p>
        ) : (
          <ul className="space-y-1">
            {sData?.data.map((s) => (
              <li key={s.id} className="rounded border px-3 py-2">
                {s.name}
                <span className="ml-2 text-sm text-gray-400">{s.type}</span>
              </li>
            ))}
          </ul>
        )}
      </section>
    </main>
  );
}
