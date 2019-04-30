(async () => {
  const deployUtils = require("./deployUtils");

  const deployStage = process.env.DEPLOY_STAGE;
  const useInDocker = process.env.USE_IN_DOCKER;
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
  };

  if (deployStage === "local" || useInDocker === "true") {
    params.profile = deployUtils.findAwsProfile(deployStage);
  }

  if (useInDocker === "true") {
    params.addParams = {};
    params.addParams.USE_IN_DOCKER = "true";
  }

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
