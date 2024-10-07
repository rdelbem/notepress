import React, { useEffect, useRef, useState } from "react";
import "@mdxeditor/editor/style.css";
import {
  MDXEditor,
  UndoRedo,
  BoldItalicUnderlineToggles,
  toolbarPlugin,
  listsPlugin,
  ListsToggle,
  BlockTypeSelect,
  tablePlugin,
  InsertTable,
  linkDialogPlugin,
  CreateLink,
  headingsPlugin,
  markdownShortcutPlugin,
  thematicBreakPlugin,
  InsertThematicBreak,
  MDXEditorMethods,
  Button,
  imagePlugin,
} from "@mdxeditor/editor";
import { useParams } from "react-router-dom";
import { api, response } from "../../utils/fetch";
import LoadingBar from "react-top-loading-bar";
import {  theme } from "../../colors";
import { Note } from "../../types";

export const Editor = () => {
  const { id } = useParams();
  const [note, setNote] = useState<response<Note> | null>(null);
  const [progress, setProgress] = useState(0);
  const ref = useRef<MDXEditorMethods>(null);

  useEffect(() => {
    const fetchNote = async () => {
      setProgress(30);
      try {
        const response = await api.get<Note>(`notes/${id}`);
        if(response && response.data) setNote(response);
      } catch (error) {
        setProgress(100);
      } finally {
        setProgress(100);
      }
    };

    fetchNote();
  }, [id]);

  const handleUpdate = (update?: string) => {
    if (update) {
      setProgress(30);
      return api
        .patch(`notes/${id}`, { post_content: update })
        .finally(() => setProgress(100));
    }
  };

  return (
    <>
      <LoadingBar
        color={theme.pallete.magenta}
        progress={progress}
        onLoaderFinished={() => setProgress(0)}
      />
      {!note?.loading && note?.data && (
          <MDXEditor
            // TODO: we need to type the data what a generic
            ref={ref}
            markdown={note.data.content}
            className="dark-theme dark-editor"
            plugins={[
              imagePlugin({
                imageUploadHandler: () => {
                  return Promise.resolve('https://picsum.photos/200/300')
                },
                imageAutocompleteSuggestions: ['https://picsum.photos/200/300', 'https://picsum.photos/200']
              }),
              toolbarPlugin({
                toolbarContents: () => (
                  <>
                    <UndoRedo />
                    <BoldItalicUnderlineToggles />
                    <ListsToggle />
                    <BlockTypeSelect />
                    <CreateLink />
                    <InsertTable />
                    <InsertThematicBreak />
                    <Button onClick={() => handleUpdate(ref.current?.getMarkdown())} style={{
                      border: `1px solid ${theme.pallete.darkYellow}`
                    }}>
                      Save
                    </Button>
                  </>
                ),
              }),
              listsPlugin(),
              tablePlugin(),
              linkDialogPlugin(),
              headingsPlugin(),
              thematicBreakPlugin(),
              markdownShortcutPlugin(),
            ]}
          />
      )}
    </>
  );
};
