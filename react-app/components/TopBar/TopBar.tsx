import React, { useState } from "react";
import styled from "styled-components";
import { theme } from "../../colors";
import { UserBox } from "../UserBox";
import { api } from "../../utils/fetch";
import { PopUp } from "../PopUp";
import { useForm } from "react-hook-form";
import * as yup from "yup";
import { yupResolver } from "@hookform/resolvers/yup";
import { CreateNoteInput, Note } from "../../types";
import { useDispatch, useSelector } from "react-redux";
import { addNote } from "../../slices/notes";
import { RootState } from "../../store";
import LoadingBar from "react-top-loading-bar";

const AddNoteButton = styled.button`
  background: ${theme.pallete.magenta};
  color: white;
  border: 1px solid ${theme.pallete.darkGrey};
  padding: 5px 30px;
  font: inherit;
  cursor: pointer;
  outline: inherit;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: bold;
  margin-right: 1rem;
  height: 40px;
`;
const UserBoxContainer = styled.div`
  margin: 0 1rem;
`;
const PopUpButton = styled.button`
  background: ${theme.pallete.darkYellow};
  color: ${theme.text.color.white};
  border: 1px solid ${theme.pallete.darkGrey};
  padding: 5px;
  font: inherit;
  cursor: pointer;
  outline: inherit;
  border-radius: 5px;
  font-size: 0.75rem;
  font-weight: bold;
  margin-right: 5px;
  margin-top: 0.5rem;
`;
const InputContainer = styled.div`
  display: flex;
  flex-direction: column;

  & input {
    min-width: 250px;
    background-color: ${theme.pallete.darkGrey};
    border: 1px solid ${theme.pallete.darkGrey};
    padding: 0.6rem;
    font-size: 1rem;
    caret-color: ${theme.pallete.darkYellow};
    color: white;
    margin-bottom: 1rem;

    &:focus {
      outline: none;
      border: 1px solid ${theme.pallete.darkYellow};
    }
  }
  & label {
    font-size: 0.8rem;
  }
`;
const LabelSmall = styled.small`
  font-size: 0.7rem;
  color: ${theme.pallete.magenta};
  font-weight: 700;
`;

const createNoteSchema = yup.object().shape({
  title: yup.string().required(),
  workspaces: yup.string().optional(),
});

export const TopBar = () => {
  const [showModal, setShowModal] = useState(false);
  const [progress, setProgress] = useState(0)
  const term = useSelector(
    (state: RootState) => state.workspaceInView.currentTerm
  );
  const dispatch = useDispatch();
  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm({
    resolver: yupResolver(createNoteSchema),
    defaultValues: {
      workspaces: term,
    },
  });

  const onSubmit = async (inputUpdate: Omit<CreateNoteInput, 'workspaces'> & { workspaces?: string }) => {
    setShowModal(false);
    reset();
    try {
      setProgress(30)
      const response = await api.create<Note>("notes", inputUpdate);
      if (response.data) {
        dispatch(addNote(response.data));
      }
    } catch (error) {
      setProgress(100)
      console.error("Error:", error);
    }finally {
      setProgress(100)
    }
  };

  return (
    <>
      <LoadingBar
        color={theme.pallete.magenta}
        progress={progress}
        onLoaderFinished={() => setProgress(0)}
      />
      <UserBoxContainer>
        <UserBox />
      </UserBoxContainer>
      <AddNoteButton onClick={() => setShowModal(true)}>Add Note</AddNoteButton>
      {showModal ? (
        <PopUp inProp={showModal}>
          <>
            <p>Create note</p>
            <form onSubmit={handleSubmit((input) => onSubmit(input))}>
              <InputContainer>
                <label htmlFor="title">
                  Note title *
                  {errors.title && (
                    <LabelSmall color="red"> title is required</LabelSmall>
                  )}
                </label>
                <input type="text" id="title" {...register("title")} />
              </InputContainer>
              <InputContainer>
                <label htmlFor="workspace">
                  Workspace, use commas to separate multiple workspaces
                </label>
                <input
                  type="text"
                  id="workspace"
                  {...register("workspaces")}
                  defaultValue={term}
                />
              </InputContainer>
              <PopUpButton type="submit">Create</PopUpButton>
              <PopUpButton
                onClick={() => {
                  setShowModal(false);
                  reset();
                }}
                style={{
                  backgroundColor: theme.pallete.darkGrey,
                  border: "1px solid white",
                }}
              >
                Dismiss
              </PopUpButton>
            </form>
          </>
        </PopUp>
      ) : null}
    </>
  );
};
