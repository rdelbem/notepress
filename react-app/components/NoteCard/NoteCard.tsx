import React, { useState } from "react";
import { Note } from "../../types";
import styled from "styled-components";
import { lightGreen as customLightGreen, darkGrey, darkYellow } from "../../colors";
import { format } from "date-fns";
import { Link } from "react-router-dom";
import Markdown from "react-markdown";
import remarkGfm from "remark-gfm";
import { IoTrashBin } from "react-icons/io5";
import { api } from "../../slices/fetch";
import { PopUp } from "../PopUp/PopUp";

const StyledLink = styled(Link)`
  text-decoration: none;
`;
const CardContainer = styled.div`
  display: flex;
  flex-direction: column;
  border-radius: 10px;
  background-color: ${customLightGreen};
  width: 300px;
  overflow: hidden;
  height: 200px;
  padding: 10px;
`;
const Content = styled.div`
  & > p {
    margin: 0;
  }
  text-align: justify;
  height: 120px;
  overflow: hidden;
  margin-top: 10px;
`;
const Title = styled.div`
  & > p {
    margin: 0;
    font-weight: bold;
  }
`;
const DateContainer = styled.div`
  text-align: right;
  & > small {
    margin: 0;
    color: white;
    font-size: 0.8rem;
    display: block;
    color: ${darkGrey};
    font-weight: lighter;
  }
`;
const TitleAndIconsContainer = styled.div`
  justify-content: space-between;
  color: ${darkGrey};
  display: flex;
`;
const StyledButton = styled.button`
  background: none;
  color: inherit;
  border: none;
  padding: 0;
  font: inherit;
  cursor: pointer;
  outline: inherit;
`;
const PopUpButton = styled.button`
  background: ${darkYellow};
  color: white;
  border: 1px solid ${darkGrey};
  margin: 5px;
  padding: 5px;
  font: inherit;
  cursor: pointer;
  outline: inherit;
  border-radius: 5px;
  font-size: .75rem;
  font-weight: bold;
`;

export const NoteCard = ({
  content,
  title,
  created_at: createdAt,
  updated_at: updatedAt,
  id,
  removeNote,
}: Note & { removeNote: (noteId: number) => void }) => {
  const [showModal, setShowModal] = useState(false);
  const handleDelete = () => {
    api
      .delete(`notes/${id}`)
      .then((data) => !!data && removeNote(id))
      .finally(() => setShowModal(false));
  };

  return (
    <>
      <CardContainer>
        <TitleAndIconsContainer>
          <Title>
            <p>{title}</p>
          </Title>
          <StyledButton onClick={() => setShowModal(true)}>
            <IoTrashBin />
          </StyledButton>
        </TitleAndIconsContainer>
        <StyledLink to={`/notepress/editor/${id}`}>
          <Content>
            <Markdown remarkPlugins={[remarkGfm]}>{content}</Markdown>
          </Content>
        </StyledLink>
        <DateContainer>
          <small>Created at - {format(createdAt, "MM/dd/yyyy")}</small>
          <small>Updated at - {format(updatedAt, "MM/dd/yyyy")}</small>
        </DateContainer>
      </CardContainer>
      <PopUp inProp={showModal}>
        <>
          <p>Are you sure you want to delete this note?</p>
          <PopUpButton onClick={() => setShowModal(false)}>No</PopUpButton>
          <PopUpButton onClick={handleDelete}>Yes</PopUpButton>
        </>
      </PopUp>
    </>
  );
};
