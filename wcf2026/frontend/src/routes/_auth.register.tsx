import { createFileRoute, useNavigate } from '@tanstack/react-router';
import { RegisterForm } from '../features/auth/RegisterForm';

export const Route = createFileRoute('/_auth/register')({
  component: function RegisterPage() {
    const navigate = useNavigate();
    return <RegisterForm onSuccess={() => void navigate({ to: '/verify-email' })} />;
  },
});
