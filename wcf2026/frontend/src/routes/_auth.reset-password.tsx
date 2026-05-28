import { createFileRoute, useNavigate } from '@tanstack/react-router';
import { z } from 'zod';
import { ResetPasswordForm } from '../features/auth/ResetPasswordForm';

const searchSchema = z.object({
  token: z.string().optional().default(''),
  email: z.string().optional().default(''),
});

export const Route = createFileRoute('/_auth/reset-password')({
  validateSearch: searchSchema,
  component: function ResetPasswordPage() {
    const { token, email } = Route.useSearch();
    const navigate = useNavigate();
    return (
      <ResetPasswordForm
        token={token}
        email={email}
        onSuccess={() => void navigate({ to: '/login' })}
      />
    );
  },
});
