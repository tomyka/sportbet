import { createFileRoute } from '@tanstack/react-router';

export const Route = createFileRoute('/')({
  component: () => (
    <main className="p-8">
      <h1 className="text-2xl font-bold">WCF2026 — Sport Predictions</h1>
      <p className="mt-2 text-gray-600">Coming soon: tournament browser.</p>
    </main>
  ),
});
