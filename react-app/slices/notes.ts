import { PayloadAction, createAsyncThunk, createSlice } from "@reduxjs/toolkit";
import { Note } from "../types/schemas";
import { api } from "../utils/fetch";
import { State } from "./createSliceWrapper";
import { AsyncThunk, UnknownAction } from '@reduxjs/toolkit';

type AnyAsyncThunk = AsyncThunk<any, any, any>;

type RejectedWithValueActionFromAsyncThunk<Thunk extends AnyAsyncThunk> =
  ReturnType<Thunk> extends Promise<infer Result>
    ? Result extends { error: { message: string } }
      ? Result
      : never
    : never;

type FetchNotesByCategoryArg = {
  category: string;
  pageNumber: number;
};

type NotesResponse =
  | {
      pageNumber: number;
      total: number;
      notes: Note[];
    }
  | undefined;

  export const fetchNotesByCategory = createAsyncThunk<
  NotesResponse,
  FetchNotesByCategoryArg,
  { rejectValue: string }
>(
  "notes/fetchNotesByCategory",
  async ({ category, pageNumber }, { rejectWithValue }) => {
    try {
      const page = pageNumber === 0 ? 1 : pageNumber;
      const endpoint =
        category !== "all"
          ? `notes/workspace?term=${category}&page=${page}`
          : `notes?page=${page}`;
      const { data } = await api.get<NotesResponse>(endpoint);
      return data;
    } catch (error) {
      return rejectWithValue("An error occurred");
    }
  }
);

const initialState: State<NotesResponse> = {
  data: undefined,
  status: "idle",
  error: undefined,
};

const slice = createSlice({
  name: "notes",
  initialState,
  reducers: {
    removeNote: (state, action: PayloadAction<number>) => {
      if (state.data && state.data.notes) {
        state.data = {
          ...state.data,
          notes: state.data?.notes.filter((note) => note.id !== action.payload),
        };
      }
    },
    addNote: (state, action: PayloadAction<Note>) => {
      if (Array.isArray(state.data?.notes)) {
        state.data = {
          ...state.data,
          notes: [action.payload, ...state.data.notes],
          total: state.data.total + 1,
        };
      } else {
        state.data = {
          notes: [action.payload],
          pageNumber: 1,
          total: 1,
        };
      }
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(fetchNotesByCategory.pending, (state) => {
        state.status = "loading";
      })
      .addCase(fetchNotesByCategory.fulfilled, (state, action) => {
        state.status = "succeeded";
        if (state.data && state.data.notes) {
          const fetchedNoteIds = new Set(action?.payload?.notes.map(note => note.id));
          const existingNotes = state.data.notes.filter(note => !fetchedNoteIds.has(note.id));
          state.data.notes = [...existingNotes, ...(action?.payload?.notes ?? [])];
          state.data.total = action?.payload?.total ?? 0;
        } else {
          state.data = action.payload;
        }
      })
      .addCase(fetchNotesByCategory.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.payload ? action.error.message : "";
      });
  },
});

export const { removeNote, addNote } = slice.actions;

export default slice.reducer;
