(async () => {
  const deployUtils = require("./deployUtils");

  const deployStage = process.env.DEPLOY_STAGE;
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
    outputWhitelist: ["BACKEND_URL", "FRONTEND_URL", "DB_PASSWORD"],
    keyMapping: {
      BACKEND_URL: "APP_URL",
      FRONTEND_URL: "CORS_ORIGIN",
    },
    addParams: {
      APP_NAME: "qiita-stocker-backend",
      APP_ENV: deployStage,
      APP_DEBUG: true,
      LOG_CHANNEL: "stack",
      DB_CONNECTION: "mysql",
      DB_HOST: deployUtils.findDbHost(deployStage),
      DB_PORT: 3306,
      DB_DATABASE: "qiita_stocker",
      DB_USERNAME: "qiita_stocker",
      BROADCAST_DRIVER: "log",
      MAINTENANCE_MODE: deployUtils.isMaintenanceMode(),
    },
  };

  if (deployStage === "local") {
    params.profile = deployUtils.findAwsProfile();
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
