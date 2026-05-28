import { createFileRoute } from '@tanstack/react-router';
import { ForgotPasswordForm } from '../features/auth/ForgotPasswordForm';

export const Route = createFileRoute('/_auth/forgot-password')({
  component: ForgotPasswordForm,
});
