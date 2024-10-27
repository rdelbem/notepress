import React, { Fragment, useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { Note } from "../../types";
import LoadingBar from "react-top-loading-bar";
import { theme } from "../../colors";
import { NoteCard } from "../NoteCard/NoteCard";
import styled from "styled-components";
import { useDispatch, useSelector } from "react-redux";
import { AppDispatch, RootState } from "../../store";
import { fetchNotesByCategory, removeNote } from "../../slices/notes";
import ReactPaginate from "react-paginate";

const gapPadding = "1.1rem";

const Container = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: ${gapPadding};
  padding: ${gapPadding};
`;

export const NotesListView = () => {
  const { term } = useParams();
  const dispatch = useDispatch<AppDispatch>();
  const [progress, setProgress] = useState(0);
  const { data, error, status } = useSelector(
    (state: RootState) => state.notes
  );

  useEffect(() => {
    setProgress(30);
    if (term) {
      dispatch(fetchNotesByCategory({ category: term, pageNumber: 0 })).finally(
        () => setProgress(100)
      );
    } else {
      dispatch(
        fetchNotesByCategory({ category: "all", pageNumber: 0 })
      ).finally(() => setProgress(100));
    }
  }, [term, dispatch]);

  const handleRemoveNote = (noteId: number) => {
    if (data?.notes && Array.isArray(data.notes)) {
      dispatch(removeNote(noteId));
    }
  };

  const handlePageClick = ({ selected }: { selected: number }) => {
    if (term) {
      dispatch(
        fetchNotesByCategory({ category: term, pageNumber: selected + 1 })
      );
    } else {
      dispatch(
        fetchNotesByCategory({ category: "all", pageNumber: selected + 1 })
      );
    }
  };

  const filteredNotes =
    data?.notes?.filter((note: Note) => {
      if (term && note.workspaces) {
        const noteWorkspaces = note.workspaces
          .split(",")
          .map((ws) => ws.split(":")[0].trim()); // Extract workspace names
        return noteWorkspaces.includes(term);
      }
      return true;
    }) || [];

  return (
    <>
      <LoadingBar
        color={theme.pallete.magenta}
        progress={progress}
        onLoaderFinished={() => setProgress(0)}
      />
      <Container>
        {filteredNotes.length > 0 ? (
          filteredNotes.map((note: Note) => (
            <NoteCard {...note} key={note.id} removeNote={handleRemoveNote} />
          ))
        ) : (
          <p>No notes found for this workspace.</p>
        )}
      </Container>
      {data?.total && data.total > 9 ? (
        <ReactPaginate
          breakLabel="..."
          nextLabel=">"
          previousLabel="<"
          onPageChange={handlePageClick}
          pageRangeDisplayed={4}
          pageCount={Math.ceil(data.total / 9)}
          renderOnZeroPageCount={null}
        />
      ) : null}
    </>
  );
};
