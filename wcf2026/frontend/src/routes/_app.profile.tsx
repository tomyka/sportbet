import { createFileRoute } from '@tanstack/react-router';
import { ProfilePage } from '../features/auth/ProfilePage';

export const Route = createFileRoute('/_app/profile')({
  component: ProfilePage,
});
