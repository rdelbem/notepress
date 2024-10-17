
import { configureStore } from '@reduxjs/toolkit';
import workspaces from './slices/workspaces'
import notes from './slices/notes';
import workspaceInView from './slices/workspaceInView';

export const store = configureStore({
  reducer: {
    workspaceInView,
    workspaces,
    notes
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;