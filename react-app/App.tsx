import React, { useEffect } from "react";
import { SideNav } from "./components/SideNav";
import styled from "styled-components";
import { darkGrey } from "./colors";
import { useDispatch } from "react-redux";
import { fetchWorkspaceData } from "./slices/workspaces";
import { AppDispatch } from "./store";
import { NotesListView } from "./components/NotesListView/NotesListView";
import { BrowserRouter, Route, Routes, useParams } from "react-router-dom";
import { Editor } from "./components/Editor";
import { TopBar } from "./components/TopBar";

const Main = styled.div`
  display: grid;
  grid-template-columns: 15vw auto;
  background-color: ${darkGrey};
  height: 100vh;

  @media only screen and (max-width: 660px) {
    grid-template-columns: auto;
  }
`;
const Content = styled.div`
  grid-column-start: 2;
  margin-top: 95px;

  @media only screen and (max-width: 660px) {
    grid-column-start: 1;
  }
`;

export const App = () => {
  return (
    <BrowserRouter>
      <SideNav />
      <TopBar />
      <Main>
        <Content>
          <Routes>
            <Route path="/notepress/" element={<NotesListView />} />
            <Route
              path="/notepress/workspace/:term"
              element={<NotesListView />}
            />
            <Route path="/notepress/editor/:id" element={<Editor />} />
          </Routes>
        </Content>
      </Main>
    </BrowserRouter>
  );
};
