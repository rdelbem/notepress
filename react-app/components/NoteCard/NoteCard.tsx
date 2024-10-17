import React, { useState } from "react";
import { Note } from "../../types";
import styled from "styled-components";
import { theme } from "../../colors";
import { format as fromatDate } from "date-fns";
import { Link, useParams } from "react-router-dom";
import Markdown from "react-markdown";
import remarkGfm from "remark-gfm";
import { IoTrashBin } from "react-icons/io5";
import { api } from "../../utils/fetch";
import { PopUp } from "../PopUp/PopUp";

const StyledLink = styled(Link)`
  text-decoration: none;
`;
const CardContainer = styled.div`
  display: flex;
  flex-direction: column;
  border-radius: 10px;
  background-color: ${theme.pallete.lightGreen};
  width: 297px;
  overflow: hidden;
  height: 200px;
  padding: 10px;
`;
const Content = styled.div`
  & > p {
    margin: 0;
  }
  text-align: justify;
  height: 145px;
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
    color: ${theme.pallete.darkGrey};
    font-weight: lighter;
  }
`;
const TitleAndIconsContainer = styled.div`
  justify-content: space-between;
  color: ${theme.pallete.darkGrey};
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
  background: ${theme.pallete.darkYellow};
  color: ${theme.text.color.white};
  border: 1px solid ${theme.pallete.darkGrey};
  margin: 5px;
  padding: 5px;
  font: inherit;
  cursor: pointer;
  outline: inherit;
  border-radius: 5px;
  font-size: 0.75rem;
  font-weight: bold;
`;

export const NoteCard = ({
  content,
  title,
  updated_at: updatedAt,
  id,
  workspaces,
  removeNote,
}: Note & { removeNote: (noteId: number) => void }) => {
  const { term } = useParams();
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
            <p>{`${!term ? workspaces + " /" : ""}${title}`}</p>
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
          <small>{fromatDate(updatedAt, "MM/dd/yyyy")}</small>
        </DateContainer>
      </CardContainer>
      {showModal ? (
        <PopUp inProp={showModal}>
          <>
            <p>Are you sure you want to delete this note?</p>
            <PopUpButton onClick={() => setShowModal(false)}>No</PopUpButton>
            <PopUpButton
              onClick={handleDelete}
              style={{
                backgroundColor: theme.pallete.darkGrey,
                border: "1px solid white",
              }}
            >
              Yes
            </PopUpButton>
          </>
        </PopUp>
      ) : null}
    </>
  );
};
