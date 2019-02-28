(async () => {
  const deployUtils = require("./deployUtils");

  const deployStage = process.env.DEPLOY_STAGE;
  const isLocal = process.env.IS_LOCAL;
  if (deployUtils.isAllowedDeployStage(deployStage) === false) {
    return Promise.reject(
      new Error(
        "有効なステージではありません。local, dev, stg, prod が利用出来ます。"
      )
    );
  }

  const awsEnvCreator = require("@nekonomokochan/aws-env-creator");

  const params = {
    type: ".env",
    outputDir: "./",
    secretIds: deployUtils.findSecretIds(deployStage),
    region: "ap-northeast-1",
    outputWhitelist: [
      "BACKEND_URL",
      "FRONTEND_URL",
      "DB_PASSWORD",
      "BACKEND_APP_KEY",
      "NOTIFICATION_SLACK_TOKEN",
      "NOTIFICATION_SLACK_CHANNEL",
    ],
    keyMapping: {
      BACKEND_APP_KEY: "APP_KEY",
      BACKEND_URL: "APP_URL",
      FRONTEND_URL: "CORS_ORIGIN",
    },
    addParams: {
      APP_NAME: "qiita-stocker-backend",
      APP_ENV: deployStage,
      APP_DEBUG: true,
      LOG_CHANNEL: "app",
      DB_CONNECTION: "mysql",
      DB_HOST: deployUtils.findDbHost(deployStage),
      DB_PORT: 3306,
      DB_DATABASE: "qiita_stocker",
      DB_USERNAME: "qiita_stocker",
      BROADCAST_DRIVER: "log",
      MAINTENANCE_MODE: deployUtils.isMaintenanceMode(),
    },
  };

  if (deployStage === "local" || isLocal === "true") {
    params.profile = deployUtils.findAwsProfile(deployStage);
  }

  await awsEnvCreator.createEnvFile(params);
  if (deployStage === "local") {
    params.addParams.DB_DATABASE = "qiita_stocker_test";
    params.addParams.DB_USERNAME = "qiita_stocker_test";
    params.addParams.APP_ENV = "testing";
    params.outputFilename = ".env.testing";
    await awsEnvCreator.createEnvFile(params);
  }
})();
