/**
 * 許可されたデプロイステージかどうか判定する
 *
 * @param deployStage
 * @return {boolean}
 */
exports.isAllowedDeployStage = deployStage =>
  deployStage === "local" ||
  deployStage === "dev" ||
  deployStage === "stg" ||
  deployStage === "prod";

/**
 * SecretIdsを取得する
 *
 * @param deployStage
 * @return {string[]}
 */
exports.findSecretIds = deployStage => [`${deployStage}/qiita-stocker`];

/**
 * AWSのプロファイル名を取得する
 *
 * @return {string}
 */
exports.findAwsProfile = deployStage => {
  if (deployStage === "prod") {
    return "qiita-stocker-prod";
  }

  return "qiita-stocker-dev";
};

/**
 * DBのホスト名を取得する
 *
 * @param deployStage
 * @return {string}
 */
exports.findDbHost = deployStage => {
  if (deployStage === "local") {
    return "mysql";
  }

  return `qiita-stocker-db.${deployStage}`;
};

/**
 * メンテナンスモードかどうか判定する
 *
 * @return {boolean}
 */
exports.isMaintenanceMode = () => {
  return process.env.MAINTENANCE_MODE === "true";
};

/**
 * EnvFileの中身を置換する
 *
 * @param replaceParams
 */
exports.replaceEnvFile = replaceParams => {
  const fs = require("fs");
  let data = fs.readFileSync(replaceParams.outputFilename, "utf-8");

  for (const [key, value] of Object.entries(replaceParams.outputParam)) {
    data = data.replace(new RegExp(`${key}=.*`, "g"), `${key}=${value}`);
  }

  fs.unlinkSync(replaceParams.outputFilename);
  fs.appendFileSync(replaceParams.outputFilename, data);
};
