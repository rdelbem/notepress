import { createAsyncThunk, createSlice, PayloadAction } from "@reduxjs/toolkit";
import { Workspace } from "../types/schemas";
import { api } from "../utils/fetch";
import { State } from "./createSliceWrapper";

type WorkspacesResponse =
  | {
      pageNumber: number;
      total: number;
      workspaces?: Workspace[];
    }
  | undefined;

export const fetchWorkspaceData = createAsyncThunk<
  WorkspacesResponse,
  number,
  { rejectValue: string }
>(
  "fetchWorkspaceData",
  async (pageNumber: number, { rejectWithValue }) => {
    try {
      const page = pageNumber === 0 ? 1 : pageNumber;
      const { data } = await api.get<WorkspacesResponse>(
        `workspaces?page=${page}`
      );
      return data;
    } catch (error) {
      return rejectWithValue("An error occurred");
    }
  }
);

const initialState: State<WorkspacesResponse> = {
  data: {
    pageNumber: 1,
    total: 0,
    workspaces: [],
  },
  status: "idle",
  error: undefined,
};

const workspacesSlice = createSlice({
  name: "workspaces",
  initialState,
  reducers: {
    addWorkspace: (state, action: PayloadAction<Workspace>) => {
      if (state.data && state.data.workspaces) {
        const exists = state.data.workspaces.some(
          (workspace) => workspace.id === action.payload.id
        );
        if (!exists) {
          state.data.workspaces.push(action.payload);
          state.data.total += 1;
        }
      } else {
        state.data = {
          pageNumber: 1,
          total: 1,
          workspaces: [action.payload],
        };
      }
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchWorkspaceData.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchWorkspaceData.fulfilled, (state, action) => {
        state.status = "succeeded";
        if (action.payload) {
          state.data = action.payload;
        }
      })
      .addCase(fetchWorkspaceData.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload || "Failed to fetch workspaces";
      });
  },
});

export const { addWorkspace } = workspacesSlice.actions;
export default workspacesSlice.reducer;
