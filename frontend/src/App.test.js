import { render, screen } from "@testing-library/react";
import App from "./App";

test("renders login form", () => {
  render(<App />);
  expect(screen.getByRole("heading", { name: /sign in/i })).toBeInTheDocument();
  expect(screen.getByText(/quick access/i)).toBeInTheDocument();
  expect(screen.getByRole("button", { name: /login/i })).toBeInTheDocument();
});
