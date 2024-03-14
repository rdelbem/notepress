
import { configureStore } from '@reduxjs/toolkit';
import workspaces from './slices/workspaces'
import notes from './slices/notes';

export const store = configureStore({
  reducer: {
    workspaces,
    notes
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;