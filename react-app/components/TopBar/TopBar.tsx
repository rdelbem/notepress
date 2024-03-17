import React, { useState } from "react";
import styled from "styled-components";
import { darkGrey, darkYellow, grey, magenta } from "../../colors";
import { UserBox } from "../UserBox";
import { api } from "../../slices/fetch";
import { PopUp } from "../PopUp";
import { useForm } from "react-hook-form";
import * as yup from "yup";
import { yupResolver } from "@hookform/resolvers/yup";
import { CreateNoteInput, Note } from "../../types";
import { useDispatch } from "react-redux";
import { addNote } from "../../slices/notes";

const TopBarContainer = styled.div`
  position: fixed;
  width: 100%;
  height: 61px;
  border: 1px solid ${grey};
  z-index: 1000;
  background-color: ${darkGrey};
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  align-items: center;
`;
const AddNoteButton = styled.button`
  background: ${magenta};
  color: white;
  border: 1px solid ${darkGrey};
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
  background: ${darkYellow};
  color: white;
  border: 1px solid ${darkGrey};
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
    background-color: ${darkGrey};
    border: 1px solid ${darkYellow};
    padding: 0.6rem;
    font-size: 1rem;
    caret-color: ${darkYellow};
    color: white;
    margin-bottom: 1rem;

    &:focus {
      outline: none;
    }
  }
  & label {
    font-size: 0.8rem;
  }
`;
const LabelSmall = styled.small`
  font-size: 0.7rem;
  color: ${magenta};
  font-weight: 700;
`;

const createNoteSchema = yup.object().shape({
  title: yup.string().required(),
  workspaces: yup.string().optional(),
});

export const TopBar = () => {
  const [showModal, setShowModal] = useState(false);
  const dispatch = useDispatch();
  const {
    register,
    handleSubmit,
    formState: { errors },
    reset,
  } = useForm({
    resolver: yupResolver(createNoteSchema),
  });

  const onSubmit = async (inputUpdate: CreateNoteInput) => {
    setShowModal(false);
    reset();
    try {
      const response = await api.create<Note>("notes", inputUpdate);
      if(response.data) dispatch(addNote(await response.data));
    } catch (error) {
      console.error("Error:", error);
    }  
  };

  return (
    <>
      <TopBarContainer>
        <UserBoxContainer>
          <UserBox />
        </UserBoxContainer>
        <AddNoteButton onClick={() => setShowModal(true)}>
          Add Note
        </AddNoteButton>
      </TopBarContainer>
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
                <input type="text" id="workspace" {...register("workspaces")} />
              </InputContainer>
              <PopUpButton type="submit">Create</PopUpButton>
              <PopUpButton
                onClick={() => {
                  setShowModal(false);
                  reset();
                }}
                style={{
                  backgroundColor: darkGrey,
                  border: '1px solid white'
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
