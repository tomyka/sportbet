import { useState } from 'react';
import { createFileRoute } from '@tanstack/react-router';
import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import {
  fixturesQuery,
  myPredictionsQuery,
  submitScorePrediction,
  type Fixture,
  type ScorePrediction,
} from '../api/tournaments';

export const Route = createFileRoute('/_app/tournaments/$slug/predict')({
  component: PredictRoute,
});

function PredictRoute() {
  const { slug } = Route.useParams();
  return <PredictPageContent slug={slug} />;
}

export interface PredictPageContentProps {
  slug: string;
}

interface FixturePredictionRowProps {
  fixture: Fixture;
  existing: ScorePrediction | undefined;
  slug: string;
}

function FixturePredictionRow({ fixture, existing, slug }: FixturePredictionRowProps) {
  const qc = useQueryClient();
  const [home, setHome] = useState<string>(existing?.home_score?.toString() ?? '');
  const [away, setAway] = useState<string>(existing?.away_score?.toString() ?? '');
  const [saved, setSaved] = useState(false);

  const isLocked =
    fixture.status === 'live' ||
    fixture.status === 'finished' ||
    (fixture.kickoff_at !== null && new Date(fixture.kickoff_at) <= new Date());

  const mutation = useMutation({
    mutationFn: () =>
      submitScorePrediction(slug, fixture.id, parseInt(home, 10), parseInt(away, 10)),
    onSuccess: () => {
      setSaved(true);
      void qc.invalidateQueries({ queryKey: ['tournament-predictions', slug] });
      setTimeout(() => setSaved(false), 2000);
    },
  });

  if (isLocked) {
    return (
      <li className="flex items-center justify-between rounded border bg-gray-50 px-4 py-3">
        <span className="text-sm text-gray-500">Fixture #{fixture.id}</span>
        <span className="rounded bg-gray-200 px-2 py-0.5 text-xs text-gray-600">Locked</span>
        {fixture.home_score !== null && (
          <span className="text-sm font-mono">
            {fixture.home_score} – {fixture.away_score}
          </span>
        )}
      </li>
    );
  }

  return (
    <li className="flex items-center gap-3 rounded border px-4 py-3">
      <span className="min-w-0 flex-1 text-sm">Fixture #{fixture.id}</span>

      <input
        id={`home-${fixture.id}`}
        type="number"
        min={0}
        max={99}
        value={home}
        onChange={(e) => {
          setHome(e.target.value);
          setSaved(false);
        }}
        className="w-14 rounded border px-2 py-1 text-center"
        aria-label={`Home score for fixture ${fixture.id}`}
      />
      <span className="text-gray-400">–</span>
      <input
        id={`away-${fixture.id}`}
        type="number"
        min={0}
        max={99}
        value={away}
        onChange={(e) => {
          setAway(e.target.value);
          setSaved(false);
        }}
        className="w-14 rounded border px-2 py-1 text-center"
        aria-label={`Away score for fixture ${fixture.id}`}
      />

      <button
        aria-label={`Save prediction ${fixture.id}`}
        onClick={() => mutation.mutate()}
        disabled={mutation.isPending || home === '' || away === ''}
        className="rounded bg-blue-600 px-3 py-1 text-sm text-white hover:bg-blue-700 disabled:opacity-50"
      >
        {mutation.isPending ? '…' : 'Save'}
      </button>

      {saved && <span className="text-xs text-green-600">Saved!</span>}
      {mutation.isError && (
        <span className="text-xs text-red-600">
          {(mutation.error as { status?: number }).status === 423 ? 'Window closed' : 'Error'}
        </span>
      )}
    </li>
  );
}

export function PredictPageContent({ slug }: PredictPageContentProps) {
  const { data: fixtureData, isPending: fixturesPending } = useQuery(fixturesQuery(slug));
  const { data: predData, isPending: predPending } = useQuery(myPredictionsQuery(slug));

  if (fixturesPending || predPending) {
    return <p>Loading…</p>;
  }

  const fixtures = fixtureData?.data ?? [];
  const predictions = predData?.data ?? [];
  const predictionMap = new Map(predictions.map((p) => [p.fixture_id, p]));

  return (
    <main className="mx-auto max-w-2xl px-4 py-8">
      <h1 className="mb-6 text-2xl font-bold">Your Predictions</h1>
      {fixtures.length === 0 ? (
        <p className="text-sm text-gray-500">No fixtures available yet.</p>
      ) : (
        <ul className="space-y-3">
          {fixtures.map((fixture) => (
            <FixturePredictionRow
              key={fixture.id}
              fixture={fixture}
              existing={predictionMap.get(fixture.id)}
              slug={slug}
            />
          ))}
        </ul>
      )}
    </main>
  );
}
