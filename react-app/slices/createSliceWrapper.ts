import { AsyncThunk, createSlice, Draft, PayloadAction } from "@reduxjs/toolkit";

type StatusType = "idle" | "loading" | "succeeded" | "failed";

export interface State<T> {
  data: T | undefined;
  status: StatusType;
  error: Error | string | undefined;
}

export const createSliceWrapper = <T>(
  name: string,
  asyncThunk: AsyncThunk<T, void, {}>,
  initialState: State<T>
) => {
  return createSlice({
    name,
    initialState,
    reducers: {},
    extraReducers: (builder) => {
      builder
        .addCase(asyncThunk.pending, (state) => {
          state.status = "loading";
        })
        .addCase(asyncThunk.fulfilled, (state, action: PayloadAction<T>) => {
          state.status = "succeeded";
          state.data = action.payload as Draft<T>;
        })
        .addCase(asyncThunk.rejected, (state, action) => {
          state.status = "failed"; 
          state.error = action.error.message ? action.error.message : '';
        });
    },
  });
};
