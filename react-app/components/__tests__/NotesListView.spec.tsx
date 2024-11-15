// NotesListView.spec.tsx
import React from "react";
import { render, screen, fireEvent, waitFor } from "@testing-library/react";
import "@testing-library/jest-dom";
import { NotesListView } from "../NotesListView";
import { Provider } from "react-redux";
import configureMockStore from "redux-mock-store";
import { MemoryRouter, Routes, Route } from "react-router-dom";
import { fetchNotesByCategory, removeNote } from "../../slices/notes";
import ReactPaginate from "react-paginate";
import { Note } from "../../types";
import { useDispatch } from "react-redux";

jest.mock("../../slices/notes", () => ({
  fetchNotesByCategory: jest.fn((params) => ({
    type: "FETCH_NOTES_BY_CATEGORY",
    payload: params,
  })),
  removeNote: jest.fn((noteId) => ({
    type: "REMOVE_NOTE",
    payload: noteId,
  })),
}));

jest.mock("react-redux", () => ({
  ...jest.requireActual("react-redux"),
  useDispatch: jest.fn(),
}));

jest.mock("react-paginate", () => jest.fn());

const mockStore = configureMockStore();

const mockUseDispatch = jest.mocked(useDispatch);

describe("NotesListView Component", () => {
  let store: ReturnType<typeof mockStore>;
  const mockDispatch = jest.fn().mockImplementation((action) => {
    return new Promise((resolve) => {
      setTimeout(() => {
        resolve(action);
      }, 0);
    });
  });

  beforeEach(() => {
    store = mockStore({
      notes: {
        data: {
          notes: [
            {
              id: 1,
              title: "Note 1",
              content: "Content 1",
              workspaces: "WorkspaceA:1",
              author: {
                id: 1,
                display_name: "John 1",
                avatar: "https://user.com/avatar.png",
              },
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            },
            {
              id: 2,
              title: "Note 2",
              content: "Content 2",
              workspaces: "WorkspaceA:1, WorkspaceB:2",
              author: {
                id: 1,
                display_name: "John 2",
                avatar: "https://user.com/avatar.png",
              },
              created_at: new Date().toISOString(),
              updated_at: new Date().toISOString(),
            },
          ] satisfies Note[],
          total: 2,
        },
        status: "succeeded",
        error: null,
      },
    });
    jest.clearAllMocks();
    mockUseDispatch.mockReturnValue(mockDispatch);
  });

  it("dispatches fetchNotesByCategory on initial render with correct parameters", async () => {
    const term = "Workspace1";
    render(
      <Provider store={store}>
        <MemoryRouter initialEntries={[`/notes/${term}`]}>
          <Routes>
            <Route path="/notes/:term" element={<NotesListView />} />
          </Routes>
        </MemoryRouter>
      </Provider>
    );

    await waitFor(() => {
      expect(fetchNotesByCategory).toHaveBeenCalledWith({
        category: term,
        pageNumber: 0,
      });

      expect(mockDispatch).toHaveBeenCalledWith({
        type: "FETCH_NOTES_BY_CATEGORY",
        payload: { category: term, pageNumber: 0 },
      });
    });
  });

  it("renders notes when data is present", async () => {
    render(
      <Provider store={store}>
        <MemoryRouter initialEntries={[`/notes/WorkspaceA`]}>
          <Routes>
            <Route path="/notes/:term" element={<NotesListView />} />
          </Routes>
        </MemoryRouter>
      </Provider>
    );

    await waitFor(() => {
      expect(screen.getByText("Note 1")).toBeInTheDocument();
      expect(screen.getByText("Note 2")).toBeInTheDocument();
    });
  });

  it("displays message when no notes are found", async () => {
    store = mockStore({
      notes: {
        data: { notes: [], total: 0 },
        status: "succeeded",
        error: null,
      },
    });

    render(
      <Provider store={store}>
        <MemoryRouter>
          <NotesListView />
        </MemoryRouter>
      </Provider>
    );

    await waitFor(() => {
      expect(
        screen.getByText("No notes found for this workspace.")
      ).toBeInTheDocument();
    });
  });

  it("handles pagination correctly", async () => {
    store = mockStore({
      notes: {
        data: {
          notes: [],
          total: 20, // triggers pagination
        },
        status: "succeeded",
        error: null,
      },
    });

    (ReactPaginate as jest.Mock).mockImplementation(({ onPageChange }) => (
      <button onClick={() => onPageChange({ selected: 1 })}>Next</button>
    ));

    render(
      <Provider store={store}>
        <MemoryRouter>
          <NotesListView />
        </MemoryRouter>
      </Provider>
    );

    const nextPageButton = screen.getByText("Next");
    fireEvent.click(nextPageButton);

    await waitFor(() => {
      expect(fetchNotesByCategory).toHaveBeenCalledWith({
        category: "all",
        pageNumber: 2, // pageNumber starts from 0
      });
    });
  });
});
