import { useState } from 'react';
import { useMutation } from '@tanstack/react-query';
import { resendVerificationEmail } from './authApi';

export function EmailVerificationPage() {
  const [sent, setSent] = useState(false);

  const mutation = useMutation({
    mutationFn: resendVerificationEmail,
    onSuccess: () => setSent(true),
  });

  return (
    <div className="space-y-4 text-center">
      <h2 className="text-xl font-semibold">Verify your email</h2>
      <p className="text-gray-600">
        We sent you a verification link. Check your inbox and click the link to activate your account.
      </p>
      {sent ? (
        <p className="text-green-600">Email sent! Check your inbox.</p>
      ) : (
        <button
          onClick={() => mutation.mutate()}
          disabled={mutation.isPending}
          className="bg-blue-600 text-white px-4 py-2 rounded"
        >
          {mutation.isPending ? 'Sending…' : 'Resend verification email'}
        </button>
      )}
    </div>
  );
}
