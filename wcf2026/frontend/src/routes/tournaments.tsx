import { useQuery } from '@tanstack/react-query';
import { createFileRoute, Link, Outlet, useParams } from '@tanstack/react-router';
import { tournamentsQuery } from '../api/tournaments';

export const Route = createFileRoute('/tournaments')({
  component: TournamentsRoute,
});

function TournamentsRoute() {
  const { slug } = useParams({ strict: false }) as { slug?: string };

  if (slug) {
    return <Outlet />;
  }

  return <TournamentsPage useRouterLinks />;
}

interface TournamentsPageProps {
  useRouterLinks?: boolean;
}

export default function TournamentsPage({ useRouterLinks = false }: TournamentsPageProps) {
  // Tests render this page without router context, so runtime Link usage is toggled by the route wrapper.
  const { data, isPending, isError } = useQuery(tournamentsQuery());

  if (isPending) return <p>Loading...</p>;
  if (isError) return <p>Failed to load tournaments.</p>;

  return (
    <main className="mx-auto max-w-3xl p-4">
      <h1 className="mb-4 text-2xl font-bold">Tournaments</h1>
      <ul className="space-y-2">
        {data.data.map((t) => (
          <li key={t.id} className="rounded border p-3 shadow-sm">
            {useRouterLinks ? (
              <Link
                to="/tournaments/$slug"
                params={{ slug: t.slug }}
                className="text-lg font-semibold hover:underline"
              >
                {t.name}
              </Link>
            ) : (
              <a href={`/tournaments/${t.slug}`} className="text-lg font-semibold hover:underline">
                {t.name}
              </a>
            )}
            <span className="ml-2 rounded bg-gray-100 px-2 py-0.5 text-sm text-gray-600">
              {t.sport}
            </span>
          </li>
        ))}
      </ul>
    </main>
  );
}
