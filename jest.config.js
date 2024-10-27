module.exports = {
    preset: "ts-jest",
    testEnvironment: "jsdom",
    moduleNameMapper: {
      // Handle module aliases (if any)
      "^@/(.*)$": "<rootDir>/src/$1",
      "\\.(css|less|scss|sass)$": "<rootDir>/__mocks__/styleMock.js",
    },
    setupFilesAfterEnv: ["<rootDir>/jest.setup.ts"],
    transformIgnorePatterns: [
      // Ignore all modules except for @mdxeditor/editor
      "/node_modules/(?!@mdxeditor/editor)",
    ],
  };