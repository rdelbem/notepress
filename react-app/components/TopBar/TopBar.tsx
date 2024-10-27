import React, { lazy, Suspense, useState } from "react";
import styled from "styled-components";
import { theme } from "../../colors";
import { UserBox } from "../UserBox";
import { api } from "../../utils/fetch";
import { useForm } from "react-hook-form";
import * as yup from "yup";
import { yupResolver } from "@hookform/resolvers/yup";
import { CreateNoteInput, Note, Workspace } from "../../types";
import { useDispatch, useSelector } from "react-redux";
import { addNote } from "../../slices/notes";
import { RootState } from "../../store";
import LoadingBar from "react-top-loading-bar";
import { addWorkspace } from "../../slices/workspaces";

const PopUp = lazy(() => import("../PopUp"));

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

  @media only screen and (max-width: 660px) {
    display: none;
  }
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

interface HamburgerIconProps {
  isOpen: boolean;
}

const HamburgerIcon = styled.div<HamburgerIconProps>`
  display: none;
  cursor: pointer;
  width: 30px;
  height: 22px;
  position: relative;
  margin-left: 1rem;

  @media only screen and (max-width: 660px) {
    display: block;
  }

  & span {
    background: ${theme.pallete.magenta};
    position: absolute;
    height: 4px;
    width: 100%;
    border-radius: 2px;
    left: 0;
    transition: 0.25s ease-in-out;
  }

  ${(props) => `
    & span:nth-child(1) {
      top: ${props.isOpen ? "9px" : "0"};
      transform: ${props.isOpen ? "rotate(45deg)" : "rotate(0)"};
    }

    & span:nth-child(2) {
      top: 9px;
      opacity: ${props.isOpen ? "0" : "1"};
    }

    & span:nth-child(3) {
      top: ${props.isOpen ? "9px" : "18px"};
      transform: ${props.isOpen ? "rotate(-45deg)" : "rotate(0)"};
    }
  `}
`;

const createNoteSchema = yup.object().shape({
  title: yup.string().required(),
  workspaces: yup.string().optional(),
});

interface TopBarProps {
  onMenuClick: () => void;
  isSideNavOpen: boolean;
}

export const TopBar = ({ onMenuClick, isSideNavOpen }: TopBarProps) => {
  const [showModal, setShowModal] = useState(false);
  const [progress, setProgress] = useState(0);
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

  const onSubmit = async (
    inputUpdate: Omit<CreateNoteInput, "workspaces"> & { workspaces?: string }
  ) => {
    setShowModal(false);
    reset();
    try {
      setProgress(30);
      const response = await api.create<Note>("notes", inputUpdate);
      if (response.data) {
        dispatch(addNote(response.data));

        // Handle workspaces
        if (response.data.workspaces) {
          const workspaceStrings = response.data.workspaces.split(",");
          workspaceStrings.forEach((workspaceString) => {
            const [name, idString] = workspaceString.split(":");
            const id = +idString;
            if (name && !isNaN(id)) {
              const newWorkspace: Workspace = {
                id,
                name,
              };
              dispatch(addWorkspace(newWorkspace));
            }
          });
        }
      }
    } catch (error) {
      console.error("Error:", error);
    } finally {
      setProgress(100);
    }
  };

  return (
    <>
      <LoadingBar
        color={theme.pallete.magenta}
        progress={progress}
        onLoaderFinished={() => setProgress(0)}
      />
      <HamburgerIcon
        onClick={onMenuClick}
        isOpen={isSideNavOpen}
        aria-label="Toggle menu"
        aria-expanded={isSideNavOpen}
        role="button"
      >
        <span></span>
        <span></span>
        <span></span>
      </HamburgerIcon>
      <UserBoxContainer>
        <UserBox />
      </UserBoxContainer>
      <AddNoteButton onClick={() => setShowModal(true)}>Add Note</AddNoteButton>
      {showModal && (
        <Suspense fallback={<></>}>
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
        </Suspense>
      )}
    </>
  );
};
