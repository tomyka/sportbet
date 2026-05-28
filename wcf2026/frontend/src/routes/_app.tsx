import { createFileRoute, Link, Outlet, redirect, useNavigate } from '@tanstack/react-router';
import { fetchMe } from '../features/auth/authApi';
import { useAuth } from '../features/auth/useAuth';

export const Route = createFileRoute('/_app')({
  beforeLoad: async ({ location }) => {
    try {
      await fetchMe();
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
