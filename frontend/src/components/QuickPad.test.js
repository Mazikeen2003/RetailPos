import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import QuickPad from './QuickPad';

test('renders QuickPad keypad and ENTER', async () => {
  render(<QuickPad />);
  const input = screen.getByPlaceholderText(/scan or type barcode/i);
  expect(input).toBeInTheDocument();
  const btn0 = screen.getByText('0');
  expect(btn0).toBeInTheDocument();
  const enter = screen.getByRole('button', { name: /enter/i });
  expect(enter).toBeInTheDocument();

  // type and press enter
  await userEvent.click(screen.getByText('1'));
  await userEvent.click(screen.getByText('2'));
  expect(input).toHaveValue('12');
});
