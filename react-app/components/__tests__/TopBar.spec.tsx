import React from "react";
import { render, screen, fireEvent, waitFor, act } from "@testing-library/react";
import "@testing-library/jest-dom";
import { TopBar } from "../TopBar";
import { Provider } from "react-redux";
import configureStore from "redux-mock-store";
import { BrowserRouter } from "react-router-dom";
import { api } from "../../utils/fetch";
import { addNote } from "../../slices/notes";
import { addWorkspace } from "../../slices/workspaces";
import { Note } from "../../types";

jest.mock("../../utils/fetch", () => ({
  api: {
    create: jest.fn(),
  },
}));

const mockStore = configureStore([]);
const mockNote: Note = {
  id: 1,
  title: "Sample Note",
  content: "Sample content",
  updated_at: new Date().toISOString(),
  workspaces: "Workspace1:1,Workspace2:2",
  author: {
    id: 1,
    display_name: "John Doe",
    avatar: "https://user.com/avatar.png",
  },
  created_at: new Date().toISOString(),
};

beforeAll(() => {
  window.user = {
    id: 1,
    avatar: "https://user.com/avatar.png",
    display_name: "John Doe",
  };
});

describe("TopBar Component", () => {
  let store: ReturnType<typeof mockStore>;

  beforeEach(() => {
    store = mockStore({
      workspaceInView: { currentTerm: "" },
    });
    jest.clearAllMocks();
  });

  it("renders UserBox and Add Note button", () => {
    render(
      <Provider store={store}>
        <BrowserRouter>
          <TopBar isSideNavOpen={false} onMenuClick={() => {}} />
        </BrowserRouter>
      </Provider>
    );

    expect(screen.getByText("Add Note")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: /add note/i })).toBeInTheDocument();
  });

  it("opens the note creation modal when Add Note button is clicked", async () => {
    render(
      <Provider store={store}>
        <BrowserRouter>
          <TopBar isSideNavOpen={false} onMenuClick={() => {}} />
        </BrowserRouter>
      </Provider>
    );

    await act(async () => {
      fireEvent.click(screen.getByRole("button", { name: /add note/i }));
    });
    expect(screen.getByText("Create note")).toBeInTheDocument();
  });

  it("submits the form and dispatches addNote and addWorkspace actions", async () => {
    (api.create as jest.Mock).mockResolvedValue({ data: mockNote });

    render(
      <Provider store={store}>
        <BrowserRouter>
          <TopBar isSideNavOpen={false} onMenuClick={() => {}} />
        </BrowserRouter>
      </Provider>
    );

    await act(async () => {
      fireEvent.click(screen.getByRole("button", { name: /add note/i }));
    });

    fireEvent.change(screen.getByLabelText(/Note title/i), {
      target: { value: mockNote.title },
    });
    fireEvent.change(screen.getByLabelText(/Workspace/i), {
      target: { value: "Workspace1, Workspace2" },
    });

    await act(async () => {
      fireEvent.click(screen.getByRole("button", { name: /create/i }));
    });

    await waitFor(() => {
      expect(api.create).toHaveBeenCalledWith("notes", {
        title: mockNote.title,
        workspaces: "Workspace1, Workspace2",
      });
      expect(store.getActions()).toContainEqual(addNote(mockNote));
      expect(store.getActions()).toContainEqual(
        addWorkspace({ id: 1, name: "Workspace1" })
      );
      expect(store.getActions()).toContainEqual(
        addWorkspace({ id: 2, name: "Workspace2" })
      );
    });
  });

  it("closes the modal when Dismiss button is clicked", async () => {
    render(
      <Provider store={store}>
        <BrowserRouter>
          <TopBar isSideNavOpen={false} onMenuClick={() => {}} />
        </BrowserRouter>
      </Provider>
    );

    await act(async () => {
      fireEvent.click(screen.getByRole("button", { name: /add note/i }));
    });

    await act(async () => {
      fireEvent.click(screen.getByRole("button", { name: /dismiss/i }));
    });

    expect(screen.queryByText("Create note")).not.toBeInTheDocument();
  });
});
