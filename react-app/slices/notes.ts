import { PayloadAction, createAsyncThunk, createSlice } from "@reduxjs/toolkit";
import { Note } from "../types/schemas";
import { api } from "../utils/fetch";
import { State } from "./createSliceWrapper";
import {
  AnyAsyncThunk,
  RejectedWithValueActionFromAsyncThunk,
} from "@reduxjs/toolkit/dist/matchers";

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
  async (
    { category, pageNumber },
    { rejectWithValue }
  ): Promise<
    NotesResponse | RejectedWithValueActionFromAsyncThunk<AnyAsyncThunk>
  > => {
    try {
      const page = pageNumber === 0 ? 1 : pageNumber;
      const endpoint =
        category !== "all"
          ? `notes/workspace?term=${category}&page=${page}`
          : `notes?page=${page}`;
      const { data } = await api.get<NotesResponse>(endpoint);
      if (data) {
        return data;
      }
      return [];
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
          notes: [action.payload, ...state.data.notes],
          pageNumber: state.data.pageNumber,
          total: state.data.total,
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
        state.data = action.payload;
      })
      .addCase(fetchNotesByCategory.rejected, (state, action) => {
        state.status = "failed";
        state.error = action.error.message ? action.error.message : "";
      });
  },
});

export const { removeNote, addNote } = slice.actions;

export default slice.reducer;
