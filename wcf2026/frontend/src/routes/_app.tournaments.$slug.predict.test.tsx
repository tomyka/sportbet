import { describe, it, expect } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { PredictPageContent } from './_app.tournaments.$slug.predict';

function renderWithQuery(ui: React.ReactElement) {
  const qc = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });
  return render(<QueryClientProvider client={qc}>{ui}</QueryClientProvider>);
}

describe('PredictPageContent', () => {
  it('shows loading state initially', () => {
    renderWithQuery(<PredictPageContent slug="wc2026" />);
    expect(screen.getByText(/loading/i)).toBeInTheDocument();
  });

  it('renders open fixture with score inputs', async () => {
    renderWithQuery(<PredictPageContent slug="wc2026" />);
    await waitFor(() =>
      expect(screen.getByLabelText(/home score for fixture 101/i)).toBeInTheDocument()
    );
  });

  it('prefills existing prediction home=2 for fixture 101', async () => {
    renderWithQuery(<PredictPageContent slug="wc2026" />);
    await waitFor(() => {
      const input = screen.getByLabelText(/home score for fixture 101/i) as HTMLInputElement;
      expect(input.value).toBe('2');
    });
  });

  it('shows locked indicator for finished fixture 102', async () => {
    renderWithQuery(<PredictPageContent slug="wc2026" />);
    await waitFor(() =>
      expect(screen.getByText(/locked/i)).toBeInTheDocument()
    );
  });

  it('submits prediction and shows saved confirmation', async () => {
    const user = userEvent.setup();
    renderWithQuery(<PredictPageContent slug="wc2026" />);

    await waitFor(() =>
      expect(screen.getByLabelText(/home score for fixture 101/i)).toBeInTheDocument()
    );

    const homeInput = screen.getByLabelText(/home score for fixture 101/i);
    await user.clear(homeInput);
    await user.type(homeInput, '3');

    await user.click(screen.getByRole('button', { name: /save prediction 101/i }));

    await waitFor(() =>
      expect(screen.getByText(/saved/i)).toBeInTheDocument()
    );
  });
});
