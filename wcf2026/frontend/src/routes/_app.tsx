import { createFileRoute, Outlet, redirect } from '@tanstack/react-router';
import { fetchMe } from '../features/auth/authApi';

export const Route = createFileRoute('/_app')({
  beforeLoad: async ({ location }) => {
    try {
      await fetchMe();
    } catch {
      throw redirect({ to: '/login', search: { redirect: location.href } });
    }
  },
  component: () => (
    <div className="min-h-screen bg-white">
      <nav className="border-b px-6 py-3 flex justify-between">
        <span className="font-bold">WCF2026</span>
      </nav>
      <main className="p-6">
        <Outlet />
      </main>
    </div>
  ),
});
