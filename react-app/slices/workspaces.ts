import { createAsyncThunk } from "@reduxjs/toolkit";
import { Workspace } from "../types/schemas";
import { api } from "./fetch";
import { State, createSliceWrapper } from "./createSliceWrapper";

export const fetchWorkspaceData = createAsyncThunk<Workspace[]>(
  "api/fetchWorkspaceData",
  async () => {
    const { data } = await api.get("workspaces");
    return data;
  }
);

const initialState: State<Workspace[]> = {
  data: undefined,
  status: "idle",
  error: undefined,
};

export default createSliceWrapper(
  "workspaces",
  fetchWorkspaceData,
  initialState
).reducer;
