import { createFileRoute, useNavigate } from '@tanstack/react-router';
import { z } from 'zod';
import { LoginForm } from '../features/auth/LoginForm';

export const Route = createFileRoute('/_auth/login')({
  validateSearch: z.object({ redirect: z.string().optional() }),
  component: function LoginPage() {
    const navigate = useNavigate();
    const { redirect } = Route.useSearch();
    return (
      <LoginForm onSuccess={() => void navigate({ to: redirect ?? '/' })} />
    );
  },
});
