import { createAsyncThunk, createSlice } from "@reduxjs/toolkit";
import { Workspace } from "../types/schemas";
import { api } from "../utils/fetch";
import { State } from "./createSliceWrapper";
import {
  AnyAsyncThunk,
  RejectedWithValueActionFromAsyncThunk,
} from "@reduxjs/toolkit/dist/matchers";

type WorkspacesResponse =
  | {
      pageNumber: number,
      total: number,
      workspaces?: Workspace[],
    }
  | undefined;

export const fetchWorkspaceData = createAsyncThunk<WorkspacesResponse, number, {rejectValue: string;}>(
  "fetchWorkspaceData",
  async (
    pageNumber: number,
    { rejectWithValue }
  ): Promise<
    WorkspacesResponse | RejectedWithValueActionFromAsyncThunk<AnyAsyncThunk>
  > => {
    try {
      const page = pageNumber === 0 ? 1 : pageNumber
      const { data } = await api.get<WorkspacesResponse>(`workspaces?page=${page}`);
      return data;
    } catch (error) {
      return rejectWithValue("An error occurred");
    }
  }
);

const initialState: State<WorkspacesResponse> = {
  data: undefined,
  status: "idle",
  error: undefined,
};

const slice = createSlice({
  name: "workspaces",
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchWorkspaceData.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchWorkspaceData.fulfilled, (state, action) => {
        state.status = "succeeded";
        state.data = action.payload;
      })
      .addCase(fetchWorkspaceData.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.error.message ? action.error.message : "";
      });
  },
});

export default slice.reducer;
