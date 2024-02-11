import React, { useEffect, useState } from "react";
import { useParams } from "react-router-dom";
import { api, response } from "../../slices/fetch";
import { Note } from "../../types";
import LoadingBar from "react-top-loading-bar";
import { magenta } from "../../colors";
import { NoteCard } from "../NoteCard/NoteCard";
import styled from "styled-components";

const gapPadding = '1.5rem';

const Container = styled.div`
  display: flex;
  flex-wrap: wrap;
  gap: ${gapPadding};
  padding: ${gapPadding};
`

export const NotesListView = () => {
  const { term } = useParams();
  const [notes, setNotes] = useState<response | null>(null);
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    const fetchNotes = async () => {
      setProgress(30);
      try {
        const response = await api.get(term ? `notes/workspace?term=${term}` : 'notes');
        setNotes(response);
        setProgress(100);
      } catch (error) {
        setProgress(100);
        console.error("Erro ao buscar notas:", error);
      } finally {
        setProgress(100);
      }
    };

    fetchNotes();
  }, [term]);

  const removeNote = (noteId: number) => {
    if(notes?.data && Array.isArray(notes.data)){
      const updatedNotesState: Note[] = notes.data.filter((note: Note) => note.id !== noteId)
      setNotes((prev)=>{
        return {
          ...prev,
          data: updatedNotesState
        }
      })
    }
  }

  return (
    <>
      <LoadingBar
        color={magenta}
        progress={progress}
        onLoaderFinished={() => setProgress(0)}
      />
      <Container>
        {notes && Array.isArray(notes.data)
          ? notes.data.map((note: Note) => (
              <NoteCard {...note} key={note.id} removeNote={removeNote} />
            ))
          : (
            <p>No notes found for this workspace.</p>
          )}
      </Container>
    </>
  );
};
