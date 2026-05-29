import { useQuery } from '@tanstack/react-query';
import { createFileRoute, useParams } from '@tanstack/react-router';
import { NotFoundError, fixturesQuery, stagesQuery, tournamentQuery, type Fixture } from '../api/tournaments';

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
  const { data: fxData, isPending: fxPending } = useQuery({
    ...fixturesQuery(slug),
    enabled: Boolean(slug) && !tError,
  });
  const fixtures = fxData?.data ?? [];

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

      <section className="mt-6">
        <h2 className="mb-2 text-lg font-semibold">Fixtures</h2>
        {fxPending ? (
          <p>Loading fixtures...</p>
        ) : fixtures.length === 0 ? (
          <p className="text-sm text-gray-500">No fixtures scheduled yet.</p>
        ) : (
          <ul className="space-y-1">
            {fixtures.map((f: Fixture) => (
              <li key={f.id} className="flex items-center justify-between rounded border px-3 py-2">
                <span className="text-sm">#{f.id}</span>
                {f.home_score !== null ? (
                  <span className="font-mono text-sm font-semibold">
                    {f.home_score} – {f.away_score}
                  </span>
                ) : (
                  <span className="text-xs text-gray-400 capitalize">{f.status}</span>
                )}
              </li>
            ))}
          </ul>
        )}
      </section>
    </main>
  );
}
