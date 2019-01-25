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
exports.findAwsProfile = () => {
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
