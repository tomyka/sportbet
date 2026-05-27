import { useHealth } from './useHealth';

export function HealthPage() {
  const { data, isLoading, isError } = useHealth();
  if (isLoading) return <p>Loading…</p>;
  if (isError || !data) return <p>Failed to load.</p>;
  return (
    <section className="p-4">
      <h1 className="text-xl font-semibold">Backend health</h1>
      <p>
        Status: <span data-testid="health-status">{data.status}</span>
      </p>
      <p>
        DB: <span data-testid="health-db">{data.checks.database}</span>
      </p>
    </section>
  );
}
