import React, { lazy, Suspense, useContext, useState } from "react";
import { SideNav } from "./components/SideNav";
import styled from "styled-components";
import { theme } from "./colors";
import { NotesListView } from "./components/NotesListView/NotesListView";
import { BrowserRouter, Route, Routes } from "react-router-dom";
import { TopBar } from "./components/TopBar";
import { SessionContext } from "./components/SessionProvider/SessionProvider";
import Cookies from "js-cookie";
import { differenceInSeconds } from "date-fns";

const Editor = lazy(() => import("./components/Editor"));

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
  position: relative;
  z-index: 1;
  &::-webkit-scrollbar {
    background-color: transparent;
    width: 0px;
  }
  &::-webkit-scrollbar {
    width: 8px;
  }
  &::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
  }
  @media (hover: none) {
    & {
      &::-webkit-scrollbar {
        width: 8px;
      }
      &::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.2);
      }
    }
  }
`;
const StyledSideNav = styled.div<{ isOpen: boolean }>`
  overflow: hidden;
  padding: 1.5rem 1.5rem;
  min-width: 230px;
  background-color: ${theme.pallete.darkGrey};
  border-right: 1px solid ${theme.pallete.grey};
  transition: transform 0.3s ease-in-out;
  z-index: 999;

  @media only screen and (max-width: 660px) {
    position: fixed;
    top: 86px; // Height of the TopBarNav
    left: 0;
    height: calc(100vh - 94px);
    transform: ${(props) =>
      props.isOpen ? "translateX(0)" : "translateX(-100%)"};
  }
`;
const TopBarNav = styled.div`
  height: 86px;
  border-bottom: 1px solid ${theme.pallete.grey};
  background-color: ${theme.pallete.darkGrey};
  display: flex;
  justify-content: space-between;
  padding: 1rem 0;
  align-items: center;

  @media only screen and (max-width: 660px) {
    height: 74px;
    padding: 0.5rem 0;
  }
`;
const SecondRow = styled.div`
  display: flex;
  flex-direction: row;
  position: relative;
`;
const Overlay = styled.div`
  position: fixed;
  top: 86px; // Height of the TopBarNav
  left: 0;
  width: 100%;
  height: calc(100vh - 94px);
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 998;
`;

export const App = () => {
  const session = useContext(SessionContext);
  const [isSideNavOpen, setIsSideNavOpen] = useState(false);

  if (Cookies.get("np_jwt_exp")) {
    const jwtExp = parseInt(Cookies.get("np_jwt_exp") as string);
    const now = new Date();
    if (differenceInSeconds(now, jwtExp) > 0) {
      session?.setIsAuthenticated(true);
    } else {
      return (window.location.href = "/wp-login.php?action=logout");
    }
  }

  return (
    <BrowserRouter>
      <AppContainer>
        <TopBarNav>
          <TopBar
            onMenuClick={() => setIsSideNavOpen(!isSideNavOpen)}
            isSideNavOpen={isSideNavOpen}
          />
        </TopBarNav>
        <SecondRow>
          <StyledSideNav isOpen={isSideNavOpen}>
            <SideNav onLinkClick={() => setIsSideNavOpen(false)} />
          </StyledSideNav>
          <Main onClick={() => isSideNavOpen && setIsSideNavOpen(false)}>
            <Suspense fallback={<></>}>
              <Routes>
                <Route path="/notepress/" element={<NotesListView />} />
                <Route
                  path="/notepress/workspace/:term"
                  element={<NotesListView />}
                />
                <Route path="/notepress/editor/:id" element={<Editor />} />
              </Routes>
            </Suspense>
          </Main>
        </SecondRow>
        {isSideNavOpen && <Overlay onClick={() => setIsSideNavOpen(false)} />}
      </AppContainer>
    </BrowserRouter>
  );
};
