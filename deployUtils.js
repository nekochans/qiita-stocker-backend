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
