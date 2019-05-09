(async () => {
  // TODO 将来的にこのscriptはローカル環境になる、その為には以下の課題解決が必要
  // TODO https://github.com/nekochans/qiita-stocker-terraform/issues/74
  // TODO https://github.com/nekochans/qiita-stocker-terraform/issues/75
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
    parameterPath: `/${deployStage}/qiita-stocker/api`,
    region: "ap-northeast-1",
    profile: deployUtils.findAwsProfile(deployStage),
    addParams: {
      USE_IN_DOCKER: "true",
    },
  };

  await awsEnvCreator.createEnvFile(params);
  if (deployStage === "local") {
    const outputFilename = ".env.testing";
    params["outputFilename"] = outputFilename;
    await awsEnvCreator.createEnvFile(params);

    const replaceParams = {
      outputFilename: outputFilename,
      outputParam: {
        DB_DATABASE: "qiita_stocker_test",
        DB_USERNAME: "qiita_stocker_test",
        APP_ENV: "testing",
      },
    };
    deployUtils.replaceEnvFile(replaceParams);
  }
})();
