function dateDiffCalculator(serverDateInISOString) {
    const currentDate = new Date();
    const serverDate = new Date(serverDateInISOString);

    return currentDate.getTime() - serverDate.getTime();
}

export default dateDiffCalculator;
