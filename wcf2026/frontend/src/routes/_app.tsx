import { createFileRoute, Link, Outlet, redirect, useNavigate } from '@tanstack/react-router';
import type { QueryClient } from '@tanstack/react-query';
import { fetchMe } from '../features/auth/authApi';
import { useAuth } from '../features/auth/useAuth';

const ME_QUERY = { queryKey: ['auth', 'me'] as const, queryFn: fetchMe };

export const Route = createFileRoute('/_app')({
  beforeLoad: async ({ location, context }) => {
    const queryClient: QueryClient = context.queryClient;
    try {
      await queryClient.fetchQuery(ME_QUERY);
    } catch {
      throw redirect({ to: '/login', search: { redirect: location.href } });
    }
  },
  component: AppLayout,
});

function AppLayout() {
  const { user, logout } = useAuth();
  const navigate = useNavigate();

  const handleLogout = async () => {
    await logout();
    void navigate({ to: '/login' });
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <nav className="bg-white border-b px-6 py-3 flex items-center justify-between">
        <Link to="/" className="font-bold text-blue-600">WCF2026</Link>
        <div className="flex items-center gap-4">
          {user && (
            <>
              <span className="text-sm text-gray-600">{user.name}</span>
              <Link to="/profile" className="text-sm text-blue-600 hover:underline">Profile</Link>
              <button onClick={() => void handleLogout()} className="text-sm text-gray-500 hover:text-red-600">
                Sign out
              </button>
            </>
          )}
        </div>
      </nav>
      <main className="p-6">
        <Outlet />
      </main>
    </div>
  );
}
