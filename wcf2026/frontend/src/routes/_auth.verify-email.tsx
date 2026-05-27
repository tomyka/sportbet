import { createFileRoute } from '@tanstack/react-router';

export const Route = createFileRoute('/_auth/verify-email')({
  component: () => <div>Verify Email — coming in Task 7.3</div>,
});
