import { createFileRoute } from '@tanstack/react-router';

export const Route = createFileRoute('/_app/profile')({
  component: () => <div>Profile — coming in Task 8.1</div>,
});
