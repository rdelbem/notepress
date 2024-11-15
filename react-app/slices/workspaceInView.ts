import { PayloadAction, createSlice } from "@reduxjs/toolkit";

type WorkspaceInView = {
  currentTerm?: string;
};

const initialState: WorkspaceInView = {
  currentTerm: undefined,
};

const slice = createSlice({
  name: "workspacesInView",
  initialState,
  reducers: {
    setCurrentTerm: (state, action: PayloadAction<string | undefined>) => {
      state.currentTerm = action.payload;
    },
  },
});

export const { setCurrentTerm } = slice.actions;
export default slice.reducer;
