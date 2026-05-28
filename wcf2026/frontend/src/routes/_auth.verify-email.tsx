import { createFileRoute } from '@tanstack/react-router';
import { EmailVerificationPage } from '../features/auth/EmailVerificationPage';

export const Route = createFileRoute('/_auth/verify-email')({
  component: EmailVerificationPage,
});
