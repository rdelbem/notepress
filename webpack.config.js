const path = require("path");
const webpack = require("webpack");
const {
    CleanWebpackPlugin
} = require("clean-webpack-plugin");
const BrowserSyncPlugin = require("browser-sync-webpack-plugin");
const BundleAnalyzerPlugin =
    require("webpack-bundle-analyzer").BundleAnalyzerPlugin;
const TerserPlugin = require("terser-webpack-plugin");

module.exports = (env) => {
    const pathToSave = "./assets/js/prod";

    return {
        entry: path.resolve(__dirname, "./react-app/index.tsx"),
        module: {
            rules: [{
                test: /\.(ts|tsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: "babel-loader",
                    options: {
                        presets: ["@babel/preset-env", "@babel/preset-react", "@babel/preset-typescript"],
                    },
                },
            },
            {
                test: /\.css$/,
                use: ["style-loader", "css-loader"],
            },
            ],
        },
        resolve: {
            extensions: [".ts", ".tsx", ".js", ".jsx"],
        },
        output: {
            path: path.resolve(__dirname, pathToSave),
            filename: "App.js",
        },
        optimization: {
            minimize: true,
            minimizer: [new TerserPlugin()],
        },
        plugins: [
            new webpack.HotModuleReplacementPlugin(),
            new CleanWebpackPlugin(),
            new BrowserSyncPlugin({
                proxy: "localhost:8080",
                port: 3000,
                files: ["**/*.php", "**/*.ts",  "**/*.tsx", "**/*.js"],
                ghostMode: {
                    clicks: false,
                    location: false,
                    forms: false,
                    scroll: false,
                },
                injectChanges: true,
                logFileChanges: true,
                logLevel: "debug",
                logPrefix: "wepback",
                notify: true,
                reloadDelay: 0,
            }),
        ],
    };
};