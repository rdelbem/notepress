import React from "react";
import { render, screen, fireEvent, waitFor } from "@testing-library/react";
import "@testing-library/jest-dom";
import { NoteCard } from "../NoteCard";
import { api } from "../../utils/fetch";
import { BrowserRouter } from "react-router-dom";
import { Note } from "../../types";

jest.mock("../../utils/fetch", () => ({
  api: {
    delete: jest.fn(),
  },
}));

describe("NoteCard Component", () => {
  const mockNote: Note = {
    id: 1,
    title: "Sample Note",
    content: "This is a sample note content.",
    updated_at: new Date().toISOString(),
    workspaces: "Workspace1:1",
    author: {
      id: 1,
      avatar: "https://example.com/avatar.png",
      display_name: "John Doe",
    },
    created_at: new Date().toISOString(),
  };

  const mockRemoveNote = jest.fn();

  beforeEach(() => {
    jest.clearAllMocks();
  });

  it("renders the note title, content, and formatted date", () => {
    render(
      <BrowserRouter>
        <NoteCard {...mockNote} removeNote={mockRemoveNote} />
      </BrowserRouter>
    );

    expect(screen.getByText(/Sample Note/)).toBeInTheDocument();
    expect(
      screen.getByText(/This is a sample note content./)
    ).toBeInTheDocument();

    const formattedDate = new Date(mockNote.updated_at).toLocaleDateString(
      "en-US",
      { month: "2-digit", day: "2-digit", year: "numeric" }
    );
    expect(screen.getByText(formattedDate)).toBeInTheDocument();
  });

  it("shows the delete confirmation modal when delete button is clicked", async () => {
    render(
      <BrowserRouter>
        <NoteCard {...mockNote} removeNote={mockRemoveNote} />
      </BrowserRouter>
    );

    fireEvent.click(screen.getByRole("button"));
    expect(
      screen.getByText(/Are you sure you want to delete this note?/)
    ).toBeInTheDocument();
  });

  it("calls removeNote when delete is confirmed", async () => {
    (api.delete as jest.Mock).mockResolvedValue(true);

    render(
      <BrowserRouter>
        <NoteCard {...mockNote} removeNote={mockRemoveNote} />
      </BrowserRouter>
    );

    fireEvent.click(screen.getByRole("button"));

    expect(screen.getByRole("button", { name: "Yes" })).toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "Yes" }));

    await waitFor(() => {
      expect(api.delete).toHaveBeenCalledWith(`notes/${mockNote.id}`);
      expect(mockRemoveNote).toHaveBeenCalledWith(mockNote.id);
    });
  });

  it("closes the delete confirmation modal when 'No' is clicked", async () => {
    render(
      <BrowserRouter>
        <NoteCard {...mockNote} removeNote={mockRemoveNote} />
      </BrowserRouter>
    );

    fireEvent.click(screen.getByRole("button"));

    expect(screen.getByRole("button", { name: "No" })).toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "No" }));

    expect(
      screen.queryByText(/Are you sure you want to delete this note?/)
    ).not.toBeInTheDocument();
  });
});
