import { describe, it, expect } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { http, HttpResponse } from 'msw';
import { server } from '../test/server';
import CreateTournamentPage from './_app.admin.tournaments.create';

function renderWithQuery(ui: React.ReactElement) {
  const qc = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });
  return render(<QueryClientProvider client={qc}>{ui}</QueryClientProvider>);
}

describe('CreateTournamentPage', () => {
  it('renders the create form', () => {
    renderWithQuery(<CreateTournamentPage />);
    expect(screen.getByRole('heading', { name: /create tournament/i })).toBeInTheDocument();
    expect(screen.getByLabelText(/name/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/slug/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/sport/i)).toBeInTheDocument();
  });

  it('submits the form successfully', async () => {
    const user = userEvent.setup();
    renderWithQuery(<CreateTournamentPage />);

    await user.type(screen.getByLabelText(/name/i), 'Euro 2026');
    await user.type(screen.getByLabelText(/slug/i), 'euro2026');
    await user.selectOptions(screen.getByLabelText(/sport/i), 'football');
    await user.click(screen.getByRole('button', { name: /create/i }));

    await waitFor(() =>
      expect(screen.getByText(/created successfully/i)).toBeInTheDocument()
    );
  });

  it('shows validation error on empty submit', async () => {
    const user = userEvent.setup();
    renderWithQuery(<CreateTournamentPage />);
    await user.click(screen.getByRole('button', { name: /create/i }));
    await waitFor(() =>
      expect(screen.getByText(/required/i)).toBeInTheDocument()
    );
  });

  it('shows a fallback error when submit fails without field errors', async () => {
    server.use(
      http.post('*/api/v1/admin/tournaments', () =>
        HttpResponse.json({ message: 'Unauthorized' }, { status: 401 })
      ),
    );

    const user = userEvent.setup();
    renderWithQuery(<CreateTournamentPage />);

    await user.type(screen.getByLabelText(/name/i), 'Euro 2026');
    await user.type(screen.getByLabelText(/slug/i), 'euro2026');
    await user.click(screen.getByRole('button', { name: /create/i }));

    await waitFor(() =>
      expect(screen.getByText(/failed to create tournament/i)).toBeInTheDocument()
    );
  });
});
