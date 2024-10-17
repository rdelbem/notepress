import React, { useContext } from "react";
import { SideNav } from "./components/SideNav";
import styled from "styled-components";
import { theme } from "./colors";
import { NotesListView } from "./components/NotesListView/NotesListView";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { Editor } from "./components/Editor";
import { TopBar } from "./components/TopBar";
import { SessionContext } from "./components/SessionProvider/SessionProvider";
import Cookies from "js-cookie";
import { differenceInSeconds } from "date-fns";

const AppContainer = styled.div`
  border: 1px solid ${theme.pallete.grey};
  max-width: 1330px;
  height: calc(100vh - 2px);
  margin-left: auto;
  margin-right: auto;
`;
const Main = styled.div`
  background-color: ${theme.pallete.darkGrey};
  overflow-y: scroll;
  width: 100%;
  height: calc(100vh - 99px);
  &::-webkit-scrollbar {
    background-color: transparent;
    width: 0px;
  }
  &::-webkit-scrollbar {
    width: 8px;
  }
  &::-webkit-scrollbar-thumb {
    background-color: rgba(0,0,0,.2)
  }
  @media (hover: none) {
  & {
    &::-webkit-scrollbar {
      width: 8px;
    }
    &::-webkit-scrollbar-thumb {
      background-color: rgba(0,0,0,.2)
    }
  }
}
`;
const StyledSideNav = styled.div`
  overflow: hidden;
  padding: 1.5rem 1.5rem;
  min-width: 230px;
  background-color: ${theme.pallete.darkGrey};
  border-right: 1px solid ${theme.pallete.grey};

  @media only screen and (max-width: 660px) {
    display: none;
  }
`;
const TopBarNav = styled.div`
  max-height: 94px;
  border-bottom: 1px solid ${theme.pallete.grey};
  background-color: ${theme.pallete.darkGrey};
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  align-items: center;
`;
const SecondRow = styled.div`
  display: flex;
  flex-direction: row;
`;

export const App = () => {
  const session = useContext(SessionContext);

  if (Cookies.get("np_jwt_exp")) {
    const jwtExp = parseInt(Cookies.get("np_jwt_exp") as string);
    const now = new Date();
    if (differenceInSeconds(now, jwtExp) > 0) {
      session?.setIsAuthenticated(true);
    }else{
      return window.location.href = '/wp-login.php?action=logout';
    }
  }

  return (
    <BrowserRouter>
      <AppContainer>
        <TopBarNav>
          <TopBar />
        </TopBarNav>
        <SecondRow>
          <StyledSideNav>
            <SideNav />
          </StyledSideNav>
          <Main>
            <Routes>
              <Route path="/notepress/" element={<NotesListView />} />
              <Route
                path="/notepress/workspace/:term"
                element={<NotesListView />}
              />
              <Route path="/notepress/editor/:id" element={<Editor />} />
            </Routes>
          </Main>
        </SecondRow>
      </AppContainer>
    </BrowserRouter>
  );
};
