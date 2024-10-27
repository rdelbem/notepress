import React from "react";
import { render, screen, fireEvent, waitFor } from "@testing-library/react";
import "@testing-library/jest-dom";
import { SideNav } from "../SideNav";
import { Provider } from "react-redux";
import configureMockStore from "redux-mock-store";
import { BrowserRouter } from "react-router-dom";
import { fetchWorkspaceData } from "../../slices/workspaces";
import { setCurrentTerm } from "../../slices/workspaceInView";
import ReactPaginate from "react-paginate";

jest.mock("../../slices/workspaces", () => ({
  fetchWorkspaceData: jest.fn(() => ({ type: 'FETCH_WORKSPACE_DATA' })),
}));

jest.mock("../../slices/workspaceInView", () => ({
  setCurrentTerm: jest.fn((term) => ({ type: 'SET_CURRENT_TERM', payload: term })),
}));

jest.mock("react-paginate", () => jest.fn());

const mockStore = configureMockStore();

describe("SideNav Component", () => {
  let store: ReturnType<typeof mockStore>;

  beforeEach(() => {
    store = mockStore({
      workspaces: {
        data: {
          workspaces: [
            { id: 1, name: "Workspace1" },
            { id: 2, name: "Workspace2" },
          ],
          total: 2,
        },
        status: "succeeded",
        error: null,
      },
      workspaceInView: { currentTerm: undefined },
    });
    jest.clearAllMocks();
  });

  it("dispatches fetchWorkspaceData on initial render", () => {
    store = mockStore({
      workspaces: { data: null, status: "idle", error: null },
    });

    render(
      <Provider store={store}>
        <BrowserRouter>
          <SideNav />
        </BrowserRouter>
      </Provider>
    );

    expect(fetchWorkspaceData).toHaveBeenCalledWith(0);
  });

  it("renders workspace links and All notes link", () => {
    render(
      <Provider store={store}>
        <BrowserRouter>
          <SideNav />
        </BrowserRouter>
      </Provider>
    );

    expect(screen.getByText("All notes")).toBeInTheDocument();
    expect(screen.getByText("Workspace1")).toBeInTheDocument();
    expect(screen.getByText("Workspace2")).toBeInTheDocument();
  });

  it("displays loading state when status is 'loading'", () => {
    store = mockStore({
      workspaces: { data: null, status: "loading", error: null },
    });

    render(
      <Provider store={store}>
        <BrowserRouter>
          <SideNav />
        </BrowserRouter>
      </Provider>
    );

    expect(screen.queryByText("All notes")).not.toBeInTheDocument();
  });

  it("displays error message when error is present", () => {
    store = mockStore({
      workspaces: { data: null, status: "failed", error: new Error("Failed to fetch workspaces") },
    });

    render(
      <Provider store={store}>
        <BrowserRouter>
          <SideNav />
        </BrowserRouter>
      </Provider>
    );

    expect(screen.getByText("Unable to get workspaces.")).toBeInTheDocument();
  });

  it("dispatches setCurrentTerm when a workspace link is clicked", async () => {
    render(
      <Provider store={store}>
        <BrowserRouter>
          <SideNav />
        </BrowserRouter>
      </Provider>
    );

    const workspaceLink = screen.getByText("Workspace1");
    fireEvent.click(workspaceLink);

    await waitFor(() => {
      expect(setCurrentTerm).toHaveBeenCalledWith("Workspace1");
    });
  });

  it("dispatches fetchWorkspaceData with correct page number when pagination is clicked", async () => {
    store = mockStore({
      workspaces: {
        data: {
          workspaces: [
            { id: 1, name: "Workspace1" },
            { id: 2, name: "Workspace2" },
          ],
          total: 15, // triggers pagination
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
        <BrowserRouter>
          <SideNav />
        </BrowserRouter>
      </Provider>
    );

    const nextPageButton = screen.getByText("Next");
    fireEvent.click(nextPageButton);

    await waitFor(() => {
      expect(fetchWorkspaceData).toHaveBeenCalledWith(2); // page count starts from 1
    });
  });
});
